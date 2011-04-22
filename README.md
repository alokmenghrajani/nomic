What is Nomic?
==============

The following quote describe Nomic pretty well:

"Nomic is a game in which changing the rules is a move. In that respect it differs from almost every other game. The
primary activity of Nomic is proposing changes in the rules, debating the wisdom of changing them in that way, voting
on the changes, deciding what can and cannot be done afterwards, and doing it. Even this core of the game, of course,
can be changed."

-- Peter Suber


What is this going on github.com?
=================================

I implemented Nomic on Amazon's EC2 instance as PHP code. You can interact with Nomic at the following url:
http://nomic.quaxio.com/

Initially, making a change was as simple as pasting a patch in the form and submitting it. The change
would automatically be reflected on http://github.com/alokmenghrajani/nomic.

I then added the requirement for every patch to be signed with GPG, using a democratique system
(a strict majority of signatures are required for a patch to be considered valid).

What is the whole point of this?
================================

I do not know. This is an experiment. It is currently running on PHP code, but there's nothing preventing
things from changing.

What are the rules regarding "out-of-band" changes?
===================================================

I currently have shell access to EC2 instance. At some point, once I consider this experiment as mature enough,
I will remove this access.

Getting started:
================

Checkout the code:
------------------
git clone git://github.com/alokmenghrajani/nomic.git

Running the code:
-----------------
Currently, you will need an Apache server with XHP.

Configure the root to serve the git checkout and you'll be all set.

Submitting a patch:
-------------------

I'm currently using the following command to submit patches:

git format-patch HEAD^
gpg -a -b <patch_file>
curl --data-urlencode patch@<patch_file> --data-urlencode sigs@<patch_file>.asc http://nomic.quaxio.com/index.php

