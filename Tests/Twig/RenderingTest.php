<?php

namespace Tests\Twig;

use Gitonomy\Bundle\GitBundle\Twig\GitExtension;
use Gitonomy\Git\Admin;
use Gitonomy\Git\Repository;
use Gitonomy\Git\Tests\AbstractTest;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Translation\Translator;

class RenderingTest extends AbstractTest
{
    private static $templates = array(
        'label_tag'    => '{{ git_label(tag) }}',
        'label_branch' => '{{ git_label(branch) }}',
    );

    public function testLabel_Tag()
    {
        $repository = self::getFoobarRepository();
        $tag = $repository->getReferences()->getTag('0.1');

        $crawler = self::render('label_tag', array(
            'tag' => $tag
        ));

        $this->assertCount(1, $crawler->filter('span.ref.tag'));
    }

    public function testLabel_Branch()
    {
        $repository = self::getFoobarRepository();
        $branch = $repository->getReferences()->getBranch('master');

        $crawler = self::render('label_branch', array(
            'branch' => $branch
        ));

        $span = $crawler->filter('span');

        $this->assertCount(1, $crawler->filter('.ref.branch'));
        $this->assertCount(0, $crawler->filter('.ref.branch.remote'));

        $branch = $repository->getReferences()->getRemoteBranch('origin/master');

        $crawler = self::render('label_branch', array(
            'branch' => $branch
        ));

        $this->assertCount(1, $crawler->filter('span.ref.branch.remote'));
    }

    private function render($template, $parameters = array())
    {
        $twig = self::getTwig();

        $result = $twig->render($template, $parameters);

        return new Crawler($result);
    }

    private static function getTwig()
    {
        static $twig;

        if (null === $twig) {
            $filesystemLoader = new \Twig_Loader_Filesystem();

            $loader = new \Twig_Loader_Chain(array(
                new \Twig_Loader_Array(self::$templates),
                $filesystemLoader
            ));

            $twig = new \Twig_Environment($loader, array(
                'strict_variables' => true
            ));

            $translator = new Translator('en_US');

            $twig->addExtension(new GitExtension(null, array('@GitBundle/default_theme.html.twig')));
            $twig->addExtension(new TranslationExtension($translator));

            $filesystemLoader->addPath(__DIR__.'/../../Resources/views', 'GitBundle');
        }

        return $twig;
    }

    private static function getFoobarRepository()
    {
        static $foobar;

        if (null === $foobar) {
            $foobar = self::createFoobarRepository(false);
        }

        return $foobar;
    }
}
