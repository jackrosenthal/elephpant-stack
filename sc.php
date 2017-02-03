<?php
require_once('db.php');

$reg_clear = $db->prepare('DELETE FROM registers WHERE name=:name');
$reg_set = $db->prepare('INSERT INTO registers VALUES (:name, :value)');
$reg_get = $db->prepare('SELECT value FROM registers WHERE name=:name');

$stack = array();
$block_depth = 0;
$lr_stack = array();

function operator_add($stack) {
    $v1 = array_pop($stack);
    $v2 = array_pop($stack);
    if (substr($v1, 0, 2) == 'a:' || substr($v2, 0, 2) == 'a:') {
        foreach (["v1", "v2"] as $v) {
            if (substr($$v, 0, 2) == 'a:') {
                $$v = unserialize($$v);
            }
            else {
                $$v = array($$v);
            }
        }
        array_push($stack, serialize(array_merge($v2, $v1)));
    }
    else {
        array_push($stack, $v1 + $v2);
    }
    return $stack;
}

function operator_mul($stack) {
    array_push($stack, array_pop($stack) * array_pop($stack));
    return $stack;
}

function operator_inv($stack) {
    array_push($stack, 1/array_pop($stack));
    return $stack;
}

function operator_sqrt($stack) {
    array_push($stack, sqrt(array_pop($stack)));
    return $stack;
}

function operator_sin($stack) {
    array_push($stack, sin(array_pop($stack)));
    return $stack;
}

function operator_cos($stack) {
    array_push($stack, cos(array_pop($stack)));
    return $stack;
}

function operator_tan($stack) {
    array_push($stack, tan(array_pop($stack)));
    return $stack;
}

function operator_log($stack) {
    array_push($stack, log(array_pop($stack)));
    return $stack;
}

function operator_sep($stack) {
    $n = array_pop($stack);
    array_push($stack, floor($n));
    array_push($stack, $n - floor($n));
    return $stack;
}

function operator_len($stack) {
    array_push($stack, count(unserialize(array_pop($stack))));
    return $stack;
}

function operator_map($lstack) {
    global $stack;
    $block = array_pop($stack);
    $vector = unserialize(array_pop($stack));
    foreach ($vector as $item) {
        array_push($stack, $item);
        array_push($stack, $block);
        operator_call($stack);
    }
    return $stack;
}

function operator_next($stack) {
    $vector = unserialize(array_pop($stack));
    $item = array_pop($vector);
    array_push($stack, serialize($vector));
    array_push($stack, $item);
    return $stack;
}

function operator_clear($stack) {
    global $reg_clear;
    $reg_clear->bindValue(':name', array_pop($stack), SQLITE3_TEXT);
    $reg_clear->execute()->finalize();
    return $stack;
}

function operator_get($stack) {
    array_push($stack, rget(array_pop($stack)));
    return $stack;
}

function operator_set($stack) {
    $rname = array_pop($stack);
    rset($rname, array_pop($stack));
    return $stack;
}

function operator_begin($stack) {
    global $block_depth;
    $block_depth += 1;
    array_push($stack, "begin");
    return $stack;
}

function operator_end($stack) {
    global $block_depth;
    $block_depth -= 1;
    if ($block_depth > 0) {
        array_push($stack, "end");
        return $stack;
    }
    $depth = 0;
    $block = array();
    while (($t = array_pop($stack)) != "begin" || $depth != 0) {
        if ($t == "end") {
            $depth += 1;
        }
        elseif ($t == "begin") {
            $depth -= 1;
        }
        array_push($block, $t);
    }
    array_push($stack, serialize(array_reverse($block)));
    return $stack;
}

function operator_call($lstack) {
    global $stack;
    exec_input(implode(" ", unserialize(array_pop($stack))));
    return $stack;
}

function operator_test($stack) {
    $greater = array_pop($stack);
    $equal = array_pop($stack);
    $less = array_pop($stack);
    $v2 = array_pop($stack);
    $v1 = array_pop($stack);
    if ($v1 < $v2) {
        array_push($stack, $less);
    }
    elseif ($v1 == $v2) {
        array_push($stack, $equal);
    }
    else {
        array_push($stack, $greater);
    }
    return $stack;
}

function rset($name, $value) {
    global $reg_clear;
    global $reg_set;
    if ($name[0] == '_') return lr_set($name, $value);
    $reg_clear->bindValue(':name', $name, SQLITE3_TEXT);
    $reg_clear->execute()->finalize();
    $reg_set->bindValue(':name', $name, SQLITE3_TEXT);
    $reg_set->bindValue(':value', $value, SQLITE3_TEXT);
    $result = $reg_set->execute();
    $result->finalize();
}

function rget($name) {
    global $reg_get;
    if ($name[0] == '_') return lr_get($name);
    $reg_get->bindValue(':name', $name, SQLITE3_TEXT);
    $result = $reg_get->execute();
    $r = $result->fetchArray()[0];
    $result->finalize();
    return $r;
}

function lr_set($name, $value) {
    global $lr_stack;
    $lr_stack[count($lr_stack) - 1][$name] = $value;
}

function lr_get($name) {
    global $lr_stack;
    foreach (array_reverse($lr_stack) as $a) {
        if (array_key_exists($name, $a)) {
            return $a[$name];
        }
    }
}

function exec_input($input) {
    global $stack;
    global $block_depth;
    global $lr_stack;
    array_push($lr_stack, array());
    $bd_store = $block_depth;
    $block_depth = 0;
    foreach (preg_split('/\s+/', $input, -1, PREG_SPLIT_NO_EMPTY) as $a) {
        if (is_numeric($a) || ($block_depth != 0 && $a != "begin" && $a != "end")) {
            array_push($stack, $a);
        }
        elseif ($a[0] == '%') {
            rset(substr($a, 1), array_pop($stack));
        }
        elseif ($a[0] == '$') {
            array_push($stack, rget(substr($a, 1)));
        }
        elseif ($a[0] == '!') {
            array_push($stack, rget(substr($a, 1)));
            operator_call($stack);
        }
        elseif (function_exists("operator_".$a)) {
            $fn = "operator_".$a;
            $stack = $fn($stack);
        }
        else {
            array_push($stack, $a);
        }
    }
    $block_depth += $bd_store;
    array_pop($lr_stack);
}

function humanize($item) {
    if (substr($item, 0, 2) == 'a:') {
        return "begin ". implode(" ", unserialize($item)) . " end";
    }
    return $item;
}

if (isset($_REQUEST["input"])) {
    try {
        exec_input($_REQUEST["input"]);
        $sc_result = "";
        foreach ($stack as $s) {
            $sc_result .= humanize($s) . " ";
        }
        if ($block_depth != 0) {
            $sc_warn = 'You aren\'t done with your input yet. <tt>$block_depth</tt> is '.$block_depth.'.';
        }
    }
    catch (Error $e) {
        $sc_result = $_REQUEST["input"];
        $sc_error = $e;
    }
}

?>
