<?php include 'sc.php'; ?>
<html>
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/superhero/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>
<div class="container">
<img class="img-responsive" src="header.jpg" />
<h1>ElePHPant Stack</h1>
<?php
if (isset($sc_warn)) {
    echo "<div class='alert alert-warning'>$sc_warn</div>";
}
if (isset($sc_error)) {
    echo "<div class='alert alert-danger'>$sc_error</div>";
}
?>
<form method="post">
  <div class="input-group input-group-lg">
    <input class="form-control input-lg" type="text" name="input" value="<?php if (isset($sc_result)) echo $sc_result;?>" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" />
    <div class="input-group-btn">
      <input type="submit" value="Evaluate" class="btn btn-primary btn-lg"/>
    </div>
  </div>
</form>
<h2>Registers</h2>
<table class="table">
<?php
require_once('db.php');
$results = $db->query('SELECT name, value FROM registers');
while ($row = $results->fetchArray()) {
    echo "<tr><td>${row[0]}</td><td><tt>".humanize($row[1])."</tt></td></tr>";
}
?>
</table>
<h2>Builtin Operators</h2>
<p><i>v1</i> <i>v2</i> <b>add</b></p>
<p>Pushes the sum of <i>v1</i> and <i>v2</i> to the stack. If either (or both) <i>v1</i> or <i>v2</i> is a block, the blocks will be concatenated (converting non-blocks to blocks if necessary).</p>
<br />
<p><i>v1</i> <i>v2</i> <b>mul</b></p>
<p>Pushes the product of <i>v1</i> and <i>v2</i> to the stack.</p>
<br />
<p><i>v</i> <b>inv</b></p>
<p>Pushes 1/<i>v</i> (the inverse of <i>v</i>) to the stack.</p>
<br />
<p><i>v</i> <b>sep</b></p>
<p>Push first the whole part of <i>v</i> then the decimal part of <i>v</i> to the stack.</p>
<br />
<p><b>begin</b>/<b>end</b></p>
<p>Start/stop creating a block, respectively. Blocks can be nested.</p>
<br />
<p><i>b</i> <b>len</b></p>
<p>Push the length of the block <i>b</i> to the stack.</p>
<br />
<p><i>b</i> <b>next</b></p>
<p>Push the top of the block <i>b</i> to the stack, keeping the remainder right below.</p>
<p>Example: <tt>begin 1 2 3 4 end next</tt> produces <tt>begin 1 2 3 end 4</tt>.</p>
<br />
<p><i>b</i> <b>call</b></p>
<p>Explode the block <i>b</i>, pushing items left-to-right to the stack (treating it like it's a clojure).</p>
<br />
<p><i>v</i> <b>sin</b>/<b>cos</b>/<b>tan</b></p>
<p>Pushes the sin/cos/tan of <i>v</i> to the stack, treating <i>v</i> as radians.</p>
<br />
<p><i>v</i> <i>r</i> <b>set</b></p>
<p>Stores <i>v</i> into register <i>r</i>. Registers which start with <tt>_</tt> are local.</p>
<br />
<p><i>r</i> <b>get</b>/<b>clear</b></p>
<p>Pushes/deletes the contents of register <i>r</i>. Registers which start with <tt>_</tt> are local.</p>
<br />
<p><i>v1</i> <i>v2</i> <i>less</i> <i>equal</i> <i>greater</i> <b>test</b></p>
<p>Pushes <i>less</i>, <i>equal</i>, or <i>greater</i> corresponding to wether <i>v1</i> is less than, equal to, or greater than <i>v2</i>.</p>
<h2>Syntactic Sugar</h2>
<p><tt>%register</tt> is a shortcut for <tt>register set</tt>.</p>
<p><tt>$register</tt> is a shortcut for <tt>register get</tt>.</p>
<p><tt>!register</tt> is a shortcut for <tt>register get call</tt>.</p>
<h2>Export Registers</h2>
<pre>
<?php
$results = $db->query('SELECT name, value FROM registers');
while ($row = $results->fetchArray()) {
    echo humanize($row[1])." %${row[0]} ";
}
$db->close();
?>
</pre>
</div>
</body>
</html>
