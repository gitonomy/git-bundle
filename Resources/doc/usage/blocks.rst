Rendering git blocks
====================

Graph log
---------

Now, you should be able to use Twig functions to render git blocks::

The PHP part:

.. code-block:: php

    $repository = new Gitonomy\Git\Repository('/path/to/repository');

    echo $twig->render('template.html.twig', array(
        'log' => $repository->getLog() # returns a log of all references
    ));

The Twig part:

.. code-block:: html+jinja

    {# Render a log graph #}
    {{ git_log(log) }}

Scroll graph with AJAX
::::::::::::::::::::::

You can paginate log on your own. If you want some Ajax love, use the option
``query_url`` when rendering the git block:

.. code-block:: html+jinja

    {{ git_log(log, {query_url: '/log-ajax'}) }}

This parameter indicates to the twig bundle to call this URL to get
fragment of lines to append. This URL will be called with "offset"
and "limit" in query string:

.. code-block:: php

    $repository = new Repository('/path/to/repository');
    $repository
        ->setOffset($_GET['offset'] ?: 0)
        ->setLimit($_GET['limit'] ?: 20)
    ;

    return $twig->render('template.html.twig', array(
        'log' => $log,
        'ajax' => true
    ));

The template:

.. code-block:: html+jinja

    {% if ajax %}
        {{ git_log_rows(log) }}
    {% else %}
        {{ git_log(log, {query_url: '/log-ajax'}) }}
    {% endif %}

Commit
------

Given you have a commit and want to display it. To do so, in PHP:

.. code-block:: php

    use Gitonomy\Git\Repository;

    $repository = new Repository('/path/to/repository');
    $commit = $repository->getHead()->getCommit();

And in your template:

.. code-block:: html+jinja

    {{ git_commit(commit) }}

This block won't display the diff by default. If you want so, explictly display
the diff block below the commit block:

.. code-block:: html+jinja

    {{ git_commit(commit) }}
    {{ git_diff(commit.diff) }}

Diff block
----------

A *Diff* can be rendered quickly:

.. code-block:: php

    use Gitonomy\Git\Repository;

    $repository = new Repository('/path/to/repository');
    $diff = $repository->getDiff('my-branch..master');

    echo $twig->render('template.html.twig', array(
        'diff' => $diff
    ));

In your twig template:

.. code-block:: html+jinja

    {{ git_diff(diff) }}

Folders & Files
---------------

Within gitlib, folders are represented by *Tree* objects and files are represented
by *Blob* object.

For both of them, functions are available to ease rendering of them.

In PHP code:

.. code-block:: php

    $commit   = $repository->getHead();
    $revision = $commit;
    $path = ''; // could be 'src/Gitonomy/Bundle'

    $tree = $revision->getTree()->resolvePath($path);

    echo $twig->render('template.twig', array(
        'tree'     => $tree,
        'path'     => $path,
        'revision' => $revision
    ));

And your template:

.. code-block:: html+jinja

    {{ git_tree(tree, revision, path) }}

Another useful function when you're rendering a tree is what is called a "pathcrumb":
a breadcrumb with all parent folders and links on them.

To generate it:

.. code-block:: html+jinja

    {{ git_pathcrumb(revision, path) }}

Blame
-----

Given a file and a revision, you want a blame view. To do so, first in PHP:

.. code-block:: php

    use Gitonomy\Git\Repository;

    $repository = new Repository('/path/to/repository');

    $blame = $repository->getBlame('master', 'path/to/file.txt');

    echo $twig->render('template.twig', array(
        'blame' => $blame
    ));

And the twig template:

.. code-block:: html+jinja

    {{ git_blame(blame) }}

Working copy status
-------------------

Given your git repository has a working copy, you can render the current status
of repository. To do so, pass the *Repository* object as first argument:

.. code-block:: html+jinja

    {{ git_status(repository) }}

Revision labels
---------------

Gitonomy GitBundle provides a way to render small label out of multiple type
of revisions: commit, tag, branch, or any *Revision* object:

To render it:

.. code-block:: html+jinja

    {{ git_label(revision) }}
