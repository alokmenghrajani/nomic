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
$sigs  = null;

if (isset($_POST['patch']) && isset($_POST['sigs'])) {
  $patch = $_POST['patch'];
  $sigs = $_POST['sigs'];
}

$body = null;
$error = null;
$ok = null;

if ($patch) {
  $pre = <pre/>;

  try {
    // copy patch to temporary file
    $patch_file = tempnam('/tmp', 'patch-');
    file_put_contents($patch_file, $patch);

    // copy sigs to temporary file
    $sigs_file = tempnam('/tmp', 'sigs-');
    file_put_contents($sigs_file, $sigs);

    // count the number of public keys
    $n_players = (int)exec('/usr/bin/gpg --homedir . --list-public-keys | grep "pub " | wc -l');

    // count the number of signatures on the patch file
    $output = array();
    $result = -1;
    exec('/usr/bin/gpg --homedir . --verify ' . $sigs_file . ' ' . $patch_file . ' 2>&1', $output, $result);
    $signatures = array();
    $marker = 'Good signature from ';
    foreach ($output as $line) {
      $pre->appendChild(<x:frag>{$line}{"\n"}</x:frag>);
      $pos = strpos($line, $marker);
      if ($pos !== false) {
        $name = substr($line, $pos + strlen($marker));
        $name = trim($name);
        $signatures[$name] = true;
      }
    }
    if ($result !== 0) {
      throw new Exception('Signature check failed');
    }
    if (count($signatures) <= floor($n_players / 2)) {
       throw new Exception('Insufficient number of signatures');
    }

    // insert signatures in patch
    $patch .= $sigs;
    file_put_contents($patch_file, $patch);

    // apply patch
    $result = my_exec('/usr/bin/git am -q --ignore-whitespace ' . $patch_file, $pre);
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
    unlink($patch_file);
    unlink($sigs_file);
  } catch (Exception $ex) {
    $error = <p style="color: red">Exception: {$ex->getMessage()}</p>;
  }

  $body =
    <x:frag>
      {$pre}
      {$error}
      {$ok}
    </x:frag>;
} else {
  $body =
    <form action="/index.php" method="post">
      Patch:<br/>
      <textarea name="patch" cols="100" rows="20"></textarea>
      <br/>
      Sigs:<br/>
      <textarea name="sigs" cols="70" rows="10"></textarea>
      <br/>
      <input type="submit"/>
    </form>;
}

echo '<!DOCTYPE html><html><head></head>';
echo $body;
echo '</html>';
