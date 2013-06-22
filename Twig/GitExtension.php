<?php

namespace Gitonomy\Bundle\GitBundle\Twig;

use Gitonomy\Bundle\GitBundle\Routing\GitUrlGeneratorInterface;
use Gitonomy\Bundle\GitBundle\Twig\TokenParser\GitThemeTokenParser;
use Gitonomy\Git\Blame;
use Gitonomy\Git\Blob;
use Gitonomy\Git\Commit;
use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Log;
use Gitonomy\Git\Reference;
use Gitonomy\Git\Reference\Branch;
use Gitonomy\Git\Reference\Stash;
use Gitonomy\Git\Reference\Tag;
use Gitonomy\Git\Repository;
use Gitonomy\Git\Revision;
use Gitonomy\Git\Tree;

/**
 * Twig extension for Git elements.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class GitExtension extends \Twig_Extension
{
    /**
     * @var GitUrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string[] an array of templates
     */
    private $themes;

    /**
     * Creates the Twig extension for git blocks.
     *
     * @param GitUrlGeneratorInterface $urlGenerator URL generator for blocks
     * @param string[]                 $themes       Twig theme files for git blocks
     */
    public function __construct(GitUrlGeneratorInterface $urlGenerator = null, array $themes = array())
    {
        $this->urlGenerator = $urlGenerator;
        $this->themes       = $themes;
    }

    public function setUrlGenerator(GitUrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public function getTokenParsers()
    {
        return array(
            // {% git_theme "my_themes.html.twig" %}
            new GitThemeTokenParser(),
        );
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            // API
            new \Twig_SimpleFunction('git_author',            array($this, 'renderAuthor'),           array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_blob',              array($this, 'renderBlob'),             array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_commit',            array($this, 'renderCommit'),           array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_diff',              array($this, 'renderDiff'),             array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_log',               array($this, 'renderLog'),              array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_log_rows',          array($this, 'renderLogRows'),          array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_pathcrumb',         array($this, 'renderPathcrumb'),        array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_tree',              array($this, 'renderTree'),             array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_blob',              array($this, 'renderBlob'),             array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_blame',             array($this, 'renderBlame'),            array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_label',             array($this, 'renderLabel'),            array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('git_url',               array($this, 'getUrl')),

            new \Twig_SimpleFunction('git_status',            array($this, 'renderStatus'),           array('is_safe' => array('html'), 'needs_environment' => true)),

            new \Twig_SimpleFunction('git_render',            array($this, 'renderBlock'),            array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('git_blob', function ($blob) { return $blob instanceof Blob; }),
            new \Twig_SimpleTest('git_reference', function ($reference) { return $reference instanceof Reference; }),
            new \Twig_SimpleTest('git_revision', function ($revision) { return $revision instanceof Revision; }),
            new \Twig_SimpleTest('git_commit', function ($commit) { return $commit instanceof Commit; }),
            new \Twig_SimpleTest('git_log', function ($log) { return $log instanceof Log; }),
            new \Twig_SimpleTest('git_tag', function ($tag) { return $tag instanceof Tag; }),
            new \Twig_SimpleTest('git_branch', function ($branch) { return $branch instanceof Branch; }),
            new \Twig_SimpleTest('git_stash', function ($stash) { return $stash instanceof Stash; }),
            new \Twig_SimpleTest('git_tree', function ($tree) { return $tree instanceof Tree; })
        );
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('git_date', array($this, 'formatDate')),
        );
    }

    public function formatDate(\DateTime $date, $format = 'long')
    {
        if ($format === 'long') {
            $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);
        } elseif ($format === 'day') {
            $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);
        } else {
            throw new \InvalidArgumentException(sprintf('Format "%s" not supported. Supported are: long'));
        }

        return $formatter->format($date);
    }

    public function renderCommit(\Twig_Environment $env, Commit $commit)
    {
        return $this->renderBlock($env, 'commit_header', array(
            'commit' => $commit,
        ));
    }

    public function renderStatus(\Twig_Environment $env, Repository $repository)
    {
        $wc = $repository->getWorkingCopy();

        return $this->renderBlock($env, 'status', array(
            'diff_staged'  => $wc->getDiffStaged(),
            'diff_pending' => $wc->getDiffPending()
        ));
    }

    public function renderLog(\Twig_Environment $env, Log $log, array $options = array())
    {
        $options = array_merge(array(
            'query_url' => null,
            'per_page'  => 20
        ), $options);

        return $this->renderBlock($env, 'log', array(
            'log'       => $log,
            'query_url' => $options['query_url'],
            'per_page'  => $options['per_page']
        ));
    }

    public function renderLogRows(\Twig_Environment $env, Log $log)
    {
        return $this->renderBlock($env, 'log_rows', array(
            'log' => $log
        ));
    }

    public function renderBlame(\Twig_Environment $env, Blame $blame)
    {
        return $this->renderBlock($env, 'blame', array(
            'blame' => $blame
        ));
    }

    public function renderDiff(\Twig_Environment $env, Diff $diff)
    {
        return $this->renderBlock($env, 'diff', array(
            'diff' => $diff
        ));
    }

    public function renderAuthor(\Twig_Environment $env, Commit $commit, array $options = array())
    {
        $options = array_merge(array(
            'size' => 15
        ), $options);

        return $this->renderBlock($env, 'author', array(
            'name'      => $commit->getAuthorName(),
            'size'      => $options['size'],
            'email'     => $commit->getAuthorEmail(),
            'email_md5' => md5($commit->getAuthorEmail())
        ));
    }

    public function renderTree(\Twig_Environment $env, Tree $tree, Revision $revision, $path = '')
    {
        return $this->renderBlock($env, 'tree', array(
            'tree'        => $tree,
            'parent_path' => substr($path, 0, strrpos($path, '/')),
            'path'        => $path,
            'revision'    => $revision
        ));
    }

    public function renderPathcrumb(\Twig_Environment $env, Revision $revision, $path = '')
    {
        return $this->renderBlock($env, 'pathcrumb', array(
            'revision'      => $revision,
            'parent_path'   => substr($path, 0, strrpos($path, '/')),
            'path'          => $path,
            'path_exploded' => explode('/', $path),
            'revision'      => $revision
        ));
    }

    public function renderBlob($env, Blob $blob)
    {
        $block = null;
        $args  = array('blob' => $blob);

        if ($blob->isText()) {
            $block = 'blob_text';
        } else {
            $mimetype = $blob->getMimetype();
            $args['mimetype'] = $mimetype;

            if (preg_match("#^image/(png|jpe?g|gif)#", $mimetype)) {
                $args['base64'] = base64_encode($blob->getContent());
                $block = 'blob_image';
            } else {
                $block = 'blob_binary';
            }
        }

        return $this->renderBlock($env, $block, $args);
    }

    public function renderLabel(\Twig_Environment $env, $revisions)
    {
        if (!is_array($revisions)) {
            $revisions = array($revisions);
        }

        $result = '';
        foreach ($revisions as $revision) {
            $result .= $this->renderBlock($env, 'git_label_revision', array('revision' => $revision));
        }

        return $result;
    }

    /**
     * Computes URL for a given value.
     *
     * @param mixed $value   the thing to make a link to
     * @param array $options options for URL generation
     */
    public function getUrl($value, array $options = array())
    {
        if (null === $this->urlGenerator) {
            return null;
        }

        if (isset($options['path'])) {
            return $this->urlGenerator->generateTreeUrl($value, $options['path']);
        }

        if ($value instanceof Commit) {
            return $this->urlGenerator->generateCommitUrl($value);
        } elseif ($value instanceof Reference) {
            return $this->urlGenerator->generateReferenceUrl($value);
        }

        throw new \InvalidArgumentException(sprintf('Unsupported type for URL generation: %s. Expected a Commit, Reference or Revision', is_object($value) ? get_class($value) : gettype($value)));
    }

    /**
     * Adds themes to the git extension.
     *
     * @param mixed $themes a theme as string or an array of themes
     */
    public function addThemes($themes)
    {
        $themes = reset($themes);
        $themes = is_array($themes) ? $themes : array($themes);
        $this->themes = array_merge($themes, $this->themes);
    }

    /**
     * Renders a block with given context.
     */
    public function renderBlock(\Twig_Environment $env, $block, $parameters = array())
    {
        foreach ($this->themes as $theme) {
            if ($theme instanceof \Twig_Template) {
                $template = $theme;
            } else {
                $template =  $env->loadTemplate($theme);
            }
            if ($template->hasBlock($block)) {
                return $this->renderTemplateBlock($env, $template, $block, $parameters);
            }
        }

        throw new \InvalidArgumentException('Unable to find block '.$block);
    }

    /**
     * Internal method for block rendering.
     */
    private function renderTemplateBlock(\Twig_Environment $env, \Twig_Template $template, $block, array $context = array())
    {
        $context = $env->mergeGlobals($context);
        $level = ob_get_level();
        ob_start();
        try {
            $rendered = $template->renderBlock($block, $context);
            ob_end_clean();

            return $rendered;
        } catch (\Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'git';
    }
}
