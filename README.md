# ElePHPant Stack

A stack based programming language with a pretty web interface.

Requirements:

* PHP 7
* php-sqlite

You will also need to edit `/etc/php/php.ini` and uncomment this line:

    extension=sqlite3.so

Running ElePHPant Stack:

    $ git clone https://github.com/jackrosenthal/elephpant-stack
    $ cd elephpant-stack
    $ php -S localhost:8080

Then visit [http://localhost:8080](http://localhost:8080).

## My Registers

Copy paste this into the evaluate box to get many of my registers.

    begin inv mul end %div begin %_1 %_2 $_1 $_2 end %swap begin %_d $_d !div sep $_d mul end %divmod begin %_v $_v $_v end %dup begin %_n $_n 2 begin $_n end begin $_n -1 add !fib $_n -2 add !fib add end !dup test call end %fib begin %_block begin end $_block begin end test end %equal begin %_block $_block begin end $_block test end %not_equal begin %_block next !swap $_block map end %reduce begin begin add end !reduce end %sum begin begin mul end !reduce end %product begin -1 mul end %neg 3.1415926535897932384626433832795 %pi 2.7182818284590452353602874713527 %e begin sep %_0 end %floor begin sep 0 0 0 1 test add end %ceil begin sep 0.5 0 1 1 test add end %round begin %_block begin end $_block !not_equal end %nonempty begin %_n $_n 0 begin -1 end begin 1 end begin $_n -1 add !factorial $_n mul end test call end %factorial begin %_n $_n 0 begin add $_n -1 add !collect end !not_equal call end %collect begin !divmod %_r %_q $_r end %mod begin !divmod %_r end %idiv begin %_e %_b $_b $_e begin $_b $_e -1 add !range $_e add end begin begin end $_b add end begin $_b $_e 1 add !range $_e add end test call end %range
