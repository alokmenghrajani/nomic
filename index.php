<?php

/**
 * "Nomic is a game in which changing the rules is a move. In that respect it
 * differs from almost every other game. The primary activity of Nomic is
 * proposing changes in the rules, debating the wisdom of changing them in that
 * way, voting on the changes, deciding what can and cannot be done afterwards,
 * and doing it. Even this core of the game, of course, can be changed."
 *
 *                                                                -- Peter Suber
 *
 *
 * I decided to start this game off in php & git... But that is only the
 * beginning...
 */

require_once 'xhp/init.php';

/**
 * Calls exec and puts the output in $pre. Returns the result.
 */
function my_exec($cmd, $pre) {
  $output = array();
  exec($cmd, $output, $result);
  foreach ($output as $line) {
    $pre->appendChild(<x:frag>{$line}</x:frag>);
  }
  return $result;
}

$patch = null;
if (isset($_POST['patch'])) {
  $patch = $_POST['patch'];
}

$body = null;
$error = null;
$ok = null;

if ($patch) {
  $pre = <pre/>;
  
  // copy patch to temporary file
  $f = tempnam('/tmp', 'patch-');
  file_put_contents($f, $patch);

  // apply patch
  $result = my_exec('/usr/bin/git am --ignore-whitespace ' . $f, $pre);
  if ($result === 0) {
    $result = my_exec('/usr/bin/git push', $pre);
    if ($result === 0) {
      $ok = <p style="color: green">patch applied!</p>;
    } else {
      $error = <p style="color: red">git push failed. Error code: {$result}.</p>;
    }
  } else {
    $error = <p style="color: red">git am failed. Error code: {$result}.</p>;
    my_exec('/usr/bin/git am --abort', $pre);
  }

  // delete temporary file
  unlink($f);

  $body =
    <x:frag>
      {$pre}
      {$error}
      {$ok}
    </x:frag>;
} else {
  $body =
    <form action="/index.php" method="post">
      <textarea name="patch" cols="100" rows="20"></textarea>
      <br/>
      <input type="submit"/>
    </form>;
}

echo '<!DOCTYPE html><html><head></head>';
echo $body;
echo '</html>';

