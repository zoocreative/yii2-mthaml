<?php

namespace mervick\mthaml;

use Yii;
use MtHaml;
use yii\web\View;
use yii\base\ViewRenderer;
use yii\base\InvalidConfigException;
use \ReflectionClass;

abstract class AbstractMtHamlViewRenderer extends ViewRenderer
{
    /**
     * @var mixed The parser
     */
    protected $parser;

    /**
     * @var string The directory or path alias pointing to where compiled templates will be stored.
     */
    public $cachePath;

    /**
     * @var bool Whether to by-pass cache, useful when debugging
     */
    public $debug = false;

    /**
     * Haml filters.
     *
     * Some MtHaml filters have runtime dependencies and are not enabled by default.
     * Such filters need to be provided to MtHaml\Environment explicitly:
     *   coffee    - compiles coffeescript to javascript
     *   less      - compiles as Lesscss
     *   scss      - converts scss to css
     *   markdown  - converts markdown to html
     *   rest      - converts reStructuredText to html
     *
     * The following filters are enabled by default:
     *   css         - wraps with style tags
     *   cdata       - wraps with CDATA markup
     *   escaped     - html escapes
     *   javascript  - wraps with script tags
     *   php         - executes the input as php code
     *   plain       - text
     *   preserve    - preserves preformatted text
     *   twig        - executes the input as twig code
     *
     * @var array
     */
    public $filters = [];

    /**
     * @var array MtHaml options
     */
    public $options = [
        'enable_escaper' => true,
    ];

    /**
     * @var array Filter classmap
     */
    protected $classmap = [
        'CoffeeScript' => [
            'filter' => '\\MtHaml\\Filter\\CoffeeScript',
            'parser' => '\\CoffeeScript\\Compiler',
            'options' => [
                'header' => [ # default: true
                    'reference' => 'filter',
                    'call' => 'compact',
                ],
                'tokens' => [ # default: null
                    'reference' => 'filter',
                    'call' => 'compact',
                ],
                'trace' => [ # default: null
                    'reference' => 'filter',
                    'filter' => ['\\Yii', 'getAlias'],
                    'call' => 'compact',
                ],
            ],
        ],
        'OyejorgeLess' => [
            'filter' => '\\MtHaml\\Filter\\Less\\OyejorgeLess',
            'parser' => '\\Less_Parser',
            'options' => [
                'compress' => [ # default: false
                    'reference' => 'parser',
                    'method' => 'SetOptions',
                    'call' => 'compact',
                ],
                'strictUnits' => [ # default: false
                    'reference' => 'parser',
                    'method' => 'SetOptions',
                    'call' => 'compact',
                ],
                'strictMath' => [ # default: false
                    'reference' => 'parser',
                    'method' => 'SetOptions',
                    'call' => 'compact',
                ],
                'relativeUrls' => [ # default: true
                    'reference' => 'parser',
                    'method' => 'SetOptions',
                    'call' => 'compact',
                ],
                'urlArgs' => [ # default: []
                    'reference' => 'parser',
                    'method' => 'SetOptions',
                    'call' => 'compact',
                ],
                'numPrecision' => [ # default: 8
                    'reference' => 'parser',
                    'method' => 'SetOptions',
                    'call' => 'compact',
                ],
                'importDirs' => [ # default: []
                    'reference' => 'parser',
                    'method' => 'SetImportDirs',
                    'accept' => 'items',
                    'filter' => ['\\Yii', 'getAlias'],
                ],
                'cacheDir' => [ # default: null
                    'reference' => 'parser',
                    'method' => 'SetCacheDir',
                    'filter' => ['\\Yii', 'getAlias'],
                ],
            ],
        ],
        'LeafoLess' => [
            'filter' => '\\MtHaml\\Filter\\Less\\LeafoLess',
            'parser' => '\\lessc',
            'options' => [
                'importDirs' => [ # default: [""]
                    'reference' => 'parser',
                    'method' => 'setImportDir',
                    'accept' => 'items',
                    'filter' => ['\\Yii', 'getAlias'],
                ],
            ],
        ],
        'Scss' => [
            'filter' => '\\MtHaml\\Filter\\Scss',
            'parser' => '\\Leafo\\ScssPhp\\Compiler',
            'options' => [
                'importDirs' => [ # default: [""]
                    'reference' => 'parser',
                    'method' => 'setImportPaths',
                    'accept' => 'items',
                    'filter' => ['\\Yii', 'getAlias'],
                ],
                'enableCompass' => [ # default: false
                    'reference' => 'self',
                    'method' => 'enableCompass',
                ],
            ],
        ],
        'MichelfMarkdown' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\MichelfMarkdown',
            'parser' => '\\Michelf\\Markdown',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
                'empty_element_suffix' => [ # default: " />"
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'tab_width' => [ # default: 4
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'no_markup' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'no_entities' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'predef_urls' => [ # default: []
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'predef_titles' => [ # default: []
                    'reference' => 'parser',
                    'call' => 'property',
                ],
            ],
        ],
        'MichelfMarkdownExtra' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\MichelfMarkdown',
            'parser' => '\\Michelf\\MarkdownExtra',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
                'empty_element_suffix' => [ # default: " />"
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'tab_width' => [ # default: 4
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'no_markup' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'no_entities' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'predef_urls' => [ # default: []
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'predef_titles' => [ # default: []
                    'reference' => 'parser',
                    'call' => 'property',
                ],
            ],
        ],
        'CebeMarkdown' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\CebeMarkdown',
            'parser' => '\\cebe\\markdown\\Markdown',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
                'html5' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
            ],
        ],
        'CebeMarkdownExtra' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\CebeMarkdown',
            'parser' => '\\cebe\\markdown\\MarkdownExtra',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
                'html5' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
            ],
        ],
        'CebeGithubMarkdown' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\CebeMarkdown',
            'parser' => '\\cebe\\markdown\\GithubMarkdown',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
                'html5' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
                'enableNewlines' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'property',
                ],
            ],
        ],
        'CiconiaMarkdown' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\Ciconia',
            'parser' => '\\Ciconia\\Ciconia',
            'parser_params_offset' => [ null ],
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
                'tabWidth' => [ # default: 4
                    'reference' => 'parser',
                    'call' => 'compact',
                ],
                'nestedTagLevel' => [ # default: 3
                    'reference' => 'parser',
                    'call' => 'compact',
                ],
                'strict' => [ # default: false
                    'reference' => 'parser',
                    'call' => 'compact',
                ],
            ],
        ],
        'CommonMark' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\CommonMark',
            'parser' => '\\League\\CommonMark\\DocParser',
            'renderer' => '\\League\\CommonMark\\HtmlRenderer',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
            ],
        ],
        'FluxBBMarkdown' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\FluxBBMarkdown',
            'parser' => '\\FluxBB\\CommonMark\\DocumentParser',
            'renderer' => '\\FluxBB\\CommonMark\\Renderer',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
            ],
        ],
        'Parsedown' => [
            'filter' => '\\MtHaml\\Filter\\Markdown\\Parsedown',
            'parser' => '\\Parsedown',
            'options' => [
                'forceOptimization' => [ # default: false
                    'reference' => 'filter',
                ],
            ],
        ],
        'ReST' => [
            'filter' => '\\MtHaml\\Filter\\ReST',
            'parser' => '\\Gregwar\\RST\\Parser',
        ],
    ];

    /**
     * Get filters.
     * @throws InvalidConfigException
     * @return array
     */
    protected function getFilters()
    {
        $filters = [];
        foreach ($this->filters as $alias => $opt) {
            if (!is_array($opt)) {
                $opt = ['filter' => $opt];
            }
            if (!isset($opt['filter'])) {
                throw new InvalidConfigException('Option "filter" required for filter alias "' . $alias . '".');
            }
            if (!isset($this->classmap[$opt['filter']])) {
                throw new InvalidConfigException('Unknown filter "' . $opt['filter'] . '".');
            }
            if (isset($opt['options'])) {
                if (!is_array($opt['options'])) {
                    throw new InvalidConfigException('Expects option "options" to be array, ' .
                        gettype($opt['options']) . ' given in filter "' . $opt['filter'] . '".');
                }
                $filters[$alias] = $this->createFilter($opt['filter'], $opt['options']);
            }
            else {
                $filters[$alias] = $this->createFilter($opt['filter']);
            }
        }
        return $filters;
    }

    /**
     * Create MtHaml filter.
     * @param string $filter Filter name
     * @param array|null $options Filter options
     * @return mixed Filter instance
     * @throws InvalidConfigException
     */
    private function createFilter($filter, $options = null)
    {
        $params = [];
        $data = $this->classmap[$filter];
        if (isset($options)) {
            if (!isset($data['options'])) {
                throw new InvalidConfigException('Filter "' . $filter . '" has no options.');
            }
            foreach ($options as $option => $value) {
                if (!isset($data['options'][$option])) {
                    throw new InvalidConfigException('Option "' . $option . '" is not defined for filter "' . $filter . '".');
                }
                $o = array_merge(['accept' => 'item', 'call' => 'param'], $data['options'][$option]);
                $ref = $o['reference'];
                if (!isset($params[$ref])) {
                    $params[$ref] = [];
                }
                if (isset($o['method']) && $o['call'] == 'param') {
                    $o['call'] = 'method';
                }
                if (!isset($o['method']) && $o['call'] == 'method') {
                    $o['method'] = $option;
                }
                if ($o['accept'] == 'items') {
                    if (!is_array($value)) {
                        throw new InvalidConfigException('Expects option "' . $option . '" to be array, ' .
                            gettype($value) . ' given in filter "' . $filter . '".');
                    }
                }
                if (isset($o['filter'])) {
                    if ($o['accept'] == 'items') {
                        foreach ($value as $k => &$v) {
                            $v = $o['filter']($v);
                        }
                    }
                    else {
                        $value = $o['filter']($value);
                    }
                }
                switch ($o['call']) {
                    case 'compact':
                        $key = isset($o['method']) ? 'method' : 'param';
                        if (!isset($params[$ref][$key])) {
                            $params[$ref][$key] = [];
                        }
                        if (isset($o['method'])) {
                            if (!isset($params[$ref][$key][$o['method']])) {
                                $params[$ref][$key][$o['method']] = [$option => $value];
                            } else {
                                array_push($params[$ref][$key][$o['method']], [$option => $value]);
                            }
                        } else {
                            $params[$ref][$key] = array_merge_recursive($params[$ref][$key], [$option => $value]);
                        }
                        break;
                    case 'method':
                        if (!isset($params[$ref]['method'])) {
                            $params[$ref]['method'] = [];
                        }
                        $params[$ref]['method'][] = [$o['method'], $value];
                        break;
                    case 'property':
                        if (!isset($params[$ref]['property'])) {
                            $params[$ref]['property'] = [];
                        }
                        $params[$ref]['property'][isset($o['property']) ? $o['property'] : $option] = $value;
                        break;
                    case 'param':
                        $params[$ref]['param'] = $value;
                        break;
                }
            }
        }
        foreach (['parser', 'renderer', 'filter'] as $type) {
            if (isset($data[$type])) {
                $args = [];
                if ($type == 'filter') {
                    array_push($args, $data['parser']);
                    if (isset($data['renderer'])) {
                        array_push($args, $data['renderer']);
                    }
                }
                if (isset($params[$type]['param'])) {
                    if (isset($data[$type.'_params_offset'])) {
                        $args += $data[$type.'_params_offset'];
                    }
                    array_push($args, $params[$type]['param']);
                }
                $object = (new ReflectionClass($data[$type]))->newInstanceArgs($args);
                if (!empty($params[$type]['method'])) {
                    foreach ($params[$type]['method'] as $name => $opt) {
                        if (is_numeric($name)) {
                            $object->$opt[0]($opt[1]);
                        } else {
                            $object->$name($opt);
                        }
                    }
                }
                if (!empty($params[$type]['property'])) {
                    foreach ($params[$type]['property'] as $property => $value) {
                        $object->$property = $value;
                    }
                }
                $data[$type] = $object;
            }
        }
        if (isset($params['self']) && !empty($params['self']['method'])) {
            foreach ($params['self']['method'] as $name => $opt) {
                if (is_numeric($name)) {
                    $this->$opt[0]($data, $opt[1]);
                } else {
                    $this->$name($data, $opt);
                }
            }
        }
        return $data['filter'];
    }

    /**
     * Enables Compass in Scss.
     * @param $data
     * @param bool $value
     */
    private function enableCompass($data, $value=false)
    {
        if ($value && !empty($data['parser'])) {
            new \scss_compass($data['parser']);
        }
    }

    /**
     * Renders a view file.
     *
     * This method is invoked by [[View]] whenever it tries to render a view.
     * Child classes must implement this method to render the given view file.
     *
     * @param View $view the view object used for rendering the file.
     * @param string $file the view file.
     * @param array $params the parameters to be passed to the view file.
     * @return string the rendering result
     */
    public function render($view, $file, $params)
    {
        return $this->parser->render($file, $params + ['app' => Yii::$app, 'mthis' => $view, 'view' => $view]);
    }

}