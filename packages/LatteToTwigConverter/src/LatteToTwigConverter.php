<?php declare(strict_types=1);

namespace Symplify\LatteToTwigConverter;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symplify\LatteToTwigConverter\Contract\CaseConverter\CaseConverterInterface;

final class LatteToTwigConverter
{
    /**
     * @var CaseConverterInterface[]
     */
    private $caseConverters = [];

    public function addCaseConverter(CaseConverterInterface $caseConverter): void
    {
        $this->caseConverters[] = $caseConverter;
    }

    public function convertFile(string $file): string
    {
        $content = FileSystem::read($file);

        foreach ($this->caseConverters as $caseConverter) {
            $content = $caseConverter->convertContent($content);
        }

        // suffix: "_snippets/menu.latte" => "_snippets/menu.twig"
        $content = Strings::replace($content, '#([A-Za-z_/"]+).latte#', '$1.twig');

        // {% if $post['rectify_post_id'] is defined %} =>
        // {% if post.rectify_post_id is defined %}
        $content = Strings::replace($content, '#({% \w+) \$(\w+)\[\'(\w+)\'\]#', '$1 $2.$3');



        $content = Strings::replace($content, '#{% (.*?) count\(\$?(\w+)\)#', '{% $1 $2|length');

        return Strings::replace($content, '#{% include \'?(\w+)\'? %}#', '{{ block(\'$1\') }}');
    }
}
