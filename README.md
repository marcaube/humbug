Humbug: Mutation Testing for PHP
================================

Humbug is a Mutation Testing framework for PHP. It is currently in development and
so, while it does actually work quite well, it will have rough edges that a team
of minions are slaving to hammer out. If it falls out of the gate, you have been
warned ;).

Mutation Testing is, in a nutshell, giving your unit tests a run for their money.
It involves injecting small defects into source code and then checking if the unit
tests noticed. If they do, then your unit tests have "killed" the mutation. If not,
the mutation has escaped detection. As unit tests are intended to prevent regressions,
having a real regression pass unnoticed would be a bad thing!

Whereas Code Coverage can tell you what code your tests are executing, Mutation
Testing is intended to help you judge how well your unit tests actually perform
and where they could be improved.

Usage
-----

Humbug is still under development so, to repeat, beware of rough edges. To ensure
a smooth ride, you should be using PHPUnit 4. You should have your phpunit
configuration file in the base of your project (same level as your source and tests
directories). If the configuration file contains a whitelist for code coverage, it
should at least cover your main source code.

Assuming humbug was cloned on same directory level as your project, from your project's
base directory:


```
../humbug/bin/humbug - h
```

In the base directory of your project create a humbug.json file:

```
{
    "timeout": 10,
    "source": {
        "directories": [
            "src"
        ]
    }
}
```

Edit as appropriate. If source files exist in the base directory, or files in
the source directories must be excluded, you can add exclude patterns (here's one
for files in base directory where composer vendor and Tests directories are excluded):

```
{
    "timeout": 10,
    "source": {
        "directories": [
            "."
        ],
        "excludes": [
            "vendor",
            "Tests"
        ]
    }
}
```

The magic command, while in your project's base directory:

```
../humbug/bin/humbug
```

If all went well, you will get something similar to:

```
 _  _            _              
| || |_  _ _ __ | |__ _  _ __ _ 
| __ | || | '  \| '_ \ || / _` |
|_||_|\_,_|_|_|_|_.__/\_,_\__, |
                          |___/ 
Humbug version 1.0-dev

Humbug running test suite to generate logs and code coverage data...

Humbug has completed the initial test run successfully.

Humbug is analysing source files...

Mutation Testing is commencing...
(.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out)

M.MMMMMSSSS.SSSM.SSSS..MSS........SMSS.M.M.TT....MM........S |   60
M..............SSMMM..M..SSSS.S..MMS............SMMSMMM.M.EM |  120
.SSSSSSSSSSSSSSSS..S..M.SS...M...S...MM......MM..SSM........ |  180
.MM...M.SM.EEE..MM...M..ME....M..ESSSSSSSSSSMEMSSSM.M.MMMM.S |  240
...MM.MM.M.MSSSSSSM.S.S..M..MSMSSSSSSSSSSS..........E..M...M |  300
MMMMMSSSM.

310 mutations were generated:
     144 mutants were killed
      69 mutants were never detected
       8 fatal errors were encountered
       2 time outs were encountered
      87 mutants were not covered by any test

Out of 223 mutants covered by tests, 68% were detected.
Out of 310 total mutations, 28% were not covered by tests.
Out of 310 total mutations, 53% were not detected.

Remember that some mutants will inevitably be harmless (i.e. false positives).

Time: 36.17 seconds Memory: 13.75MB
```

Additional detailed information about escaped mutations and errors is currently
next on the list. These will be logged to a file (given the amount of data).

Mutation Testing is typically a slow process, however Humbug implements a number
of significant optimisations. It generates code coverage data so that only tests
application to a specific mutation are run. It runs all tests in order of their
logged execution time (smallest first). It will not execute tests where code
coverage for a mutated line is nil. You may also adjust the timeout setting (if
too high for your project).

These optimisations, while requiring some upfront execution time, make Humbug
quite fast once it gets going.

The example summary results reported a number of statistics:
* 27% of mutations impacted untested code. If your Code Coverage (not reported
by Humbug) was reading 90%, then this might be really bad news. That 10% of
uncovered code has given rise to 30% of the generated regressions.
* 69% of covered testable mutations led to test failures, errors or timeouts.
This means that 31% escaped detection. Some are probably false positives, so the
bad news is often overstated a little bit.
* In combination, if you do the math, the unit tests have a combined detection
rate of ~49% (probably a bit higher in reality due to false positives). If your
code coverage were showing 90% or higher. How does that reconcile with a mutation
detection rate 41 points lower? You have tests, but are they good tests?

Interpreting these results depends on context. Humbug will (soon) log mutations
to file so they can be examined in more detail. In general, however, higher
detection rates are probably desireable. At the end of the day, if unit tests
are not capable of catching regressions, then their efficacy is impaired.


Installation
------------

Humbug currently requires the installation of the runkit extension. Yes, that scares me too!
Use the updated version at https://github.com/padraic/runkit and NOT the one you
would normally get from PECL. This has minor modifications for PHP 5.6 support from
a fork maintained by Dmitry Zenovich. For Ubuntu:

```
git clone https://github.com/padraic/runkit.git
cd runkit
phpize
./configure
make
sudo make install
sudo bash -c "echo 'extension=runkit.so' > /etc/php5/mods-available/runkit.ini"
sudo php5enmod runkit
```

We use Runkit to alter PHP methods without resorting to writing entire source code
copies all over the place. No, you are not required to install this on production
servers. Yes, it would be nice to have the PECL runkit synced to Dmitry's version.

Packagist registration will follow shortly, but for now you can clone and install
its dependencies using Composer:

```
git clone https://github.com/padraic/humbug.git
cd humbug
/path/to/composer.phar install
```

The humbug command is now at bin/humbug.

Humbug will currently work on PHP 5.4 or greater.

Performance
-----------

Mutation Testing has traditionally been slow. The concept being to re-run your test
suite for each mutation generated. To speed things up significantly, Humbug does the
following:

* On each test run, it only uses those test classes which cover the specific file
and line on which the mutation was inserted.
* It orders test classes to run so that the slowest go last (hopefully the faster
tests will detect mutations early!).
* We use Runkit because...writing many files is the opposite of fast.
* If a mutation falls on a line not covered by any tests, well, we don't bother
running any tests.
* Performance may, depending on the source code, be significantly impacted by timeouts.
The default of 60s may be far too high for smaller codebases, and far too low for
larger ones. As a rule of thumb, it shouldn't exceed the seconds needed to
normally run the tests being mutated (and can be set lower).

While all of this speeds up Humbug, do be aware that a Humbug run will be slower than
unit testing. A 2 second test suite may require 30 seconds for mutation testing. Or
5 minutes. It all depends on the interplay between lines of code, number of tests,
level of code coverage, and the performance of both code and tests.

Mutators
--------

Humbug implements a basic suite of Mutators, which essentially tells us when a
particular PHP token can be mutated, and also apply that mutation to an array
of tokens.

Binary Arithmetic:

| Original | Mutated |
| :------: |:-------:| 
| + | - |
| - | + |
| * | / |
| / | * |
| % | * |
| ** | / |
| += | -= |
| -= | += |
| & | &#124; |
| &#124; | & |
| ^ | & |
| ~ |  |
| >> | << |
| << | >> |

Boolean Substitution:

| Original | Mutated |
| :------: |:-------:| 
| true | false |
| false | true |
| && | &#124;&#124; |
| &#124;&#124; | && |

Conditional Boundaries:

| Original | Mutated
| :------: |:-------:
| >        | >=
| <        | <=
| >=       | >
| <=       | <

Negated Conditionals:

| Original | Mutated |
| :------: |:-------:| 
| == | != |
| != | == |
| <> | == |
| === | !== |
| !== | === |
| > | <= |
| < | >= |
| >= | < |
| <= | > |

Increments:

| Original | Mutated |
| :------: |:-------:| 
| ++ | -- |
| -- | ++ |

Return Values:

| Original | Mutated |
| :------: |:-------:| 
| return $this; | return null; |

Literal Numbers:

| Original | Mutated |
| :------: |:-------:| 
| 0 | 1 |
| 1 | 0 |
| Int > 1 | Int + 1 |
| Float >= 1 / <= 2 | Float + 1 |
| Float > 2 | 1 |

More Mutators will be added over time.

Did I Say Rough Edges?
----------------------

This is a short list of known issues:

* It makes assumptions about local directories which may be incorrect (read Usage).
* Configuring Humbug is by command line; a configuration file is really needed.
* PHP file parsing has a few bugs: it makes assumptions about whitespace and likely will
explode when meeting a closure. This should never interrupt a MT run, however. At worst, it will report an "E".
* An error is logged when source code references a function that does not exist (e.g. 3rd party module)
* Humbug does initial test runs, logging and code coverage. Should allow user to do that optionally.
* Test classes (not tests) are run in a specific order, fastest first. Interdependent test classes may
therefore fail regularly which will skew the results.
* Need to finalise reporting formats: text is easy, XML in progress.
* Currently 100% PHPUnit specific, well 98.237%. There is an adapter where PHPUnit code is being shovelled.
* The list of supported mutations is awaiting expansion. Yes, we need to make those unit tests scream ;).



Bah, Humbug!
============

Courtesy of [Craig Davis](https://github.com/craig-davis) who saw potential in a once empty repository :P.

```
                    .:::::::::::...
                  .::::::::::::::::::::.
                .::::::::::::::::::::::::.
               ::::::::::::::::::::::::::::.
              :::::::::::::::::::::::::::::::  .,uuu   ...
             :::::::::::::::::::::::::::::::: dHHHHHLdHHHHb
       ....:::::::'`    ::::::::::::::::::' uHHHHHHHHHHHHHF
   .uHHHHHHHHH'         ::::::::::::::`.  uHHHHHHHHHHHHHP"
   HHHHHHHHHHH          `:::::::::::',dHHuHHHHHHHHP".g@@g
  J"HHHHHHHHHP        4H ::::::::'  u$$$.
  ".HHHHHHHHP"     .,uHP :::::' uHHHHHHHHHHP"",e$$$$$c
   HHHHHHHF'      dHHHHf `````.HHHHHHHHHHP",d$$$$$$$P%C
 .dHHHP""         JHHHHbuuuu,JHHHHHHHHP",d$$$$$$$$$e=,z$$$$$$$$ee..
 ""              .HHHHHHHHHHHHHHHHHP",gdP"  ..3$$$Jd$$$$$$$$$$$$$$e.
                 dHHHHHHHHHHHHHHP".edP    " .zd$$$$$$$$$$$"3$$$$$$$$c
                 `???""??HHHHP",e$$F" .d$,?$$$$$$$$$$$$$F d$$$$$$$$F"
                       ?be.eze$$$$$".d$$$$ $$$E$$$$P".,ede`?$$$$$$$$
                      4."?$$$$$$$  z$$$$$$ $$$$r.,.e ?$$$$ $$$$$$$$$
                      '$c  "$$$$ .d$$$$$$$ 3$$$.$$$$ 4$$$ d$$$$P"`,,
                       """- "$$".`$$"    " $$f,d$$P".$$P zeee.zd$$$$$.
                     ze.    .C$C"=^"    ..$$$$$$P".$$$'e$$$$$P?$$$$$$
                 .e$$$$$$$"="$f",c,3eee$$$$$$$$P $$$P'd$$$$"..::.."?$%
                4d$$$P d$$$dF.d$$$$$$$$$$$$$$$$f $$$ d$$$" :::::::::.
               $$$$$$ d$$$$$ $$$$$$$$$$$$$$$$$$ J$$",$$$'.::::::::::::
              "$$$$$$ ?$$$$ d$$$$$$$$$$$$$$$P".dP'e$$$$':::::::::::::::
              4$$$$$$c $$$$b`$$$$$$$$$$$P"",e$$",$$$$$' ::::::::::::::::
              ' ?"?$$$b."$$$$.?$$$$$$P".e$$$$F,d$$$$$F ::::::::::::::::::
                    "?$$bc."$b.$$$$F z$$P?$$",$$$$$$$ ::::::::::::::::::::
                        `"$$c"?$$$".$$$)e$$F,$$$$$$$' ::::::::::::::::::::
                        ':. "$b...d$$P4$$$",$$$$$$$" :::::::::::::::::::::
                        ':::: "$$$$$".,"".d$$$$$$$F ::::::::::::::::::::::
                         :::: be."".d$$$4$$$$$$$$F :::::::::::::::::::::::
                          :::: "??$$$$$$$$$$?$P" :::::::::::::::::::::::::
                           :::::: ?$$$$$$$$f .::::::::::::::::::::::::::::
                            :::::::`"????"".::::::::::::::::::::::::::::::
```
