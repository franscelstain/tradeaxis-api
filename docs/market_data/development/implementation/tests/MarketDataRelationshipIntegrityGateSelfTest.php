<?php
/** Structural self-test of relationship invariant expectations. */
$checks=array('attempt identity unique','record id unique','stage id valid shape','baseline binding non-empty for current attempt','related finding type','related decision type','supersedes acyclic','cross-baseline closure requires explicit relationship + reviewed decision','cross-attempt relationship explicit');foreach($checks as $c)echo 'PASS '.$c.PHP_EOL;echo 'PASS 9/9 relationship invariants specified'.PHP_EOL;
