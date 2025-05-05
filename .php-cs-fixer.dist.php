<?php
/**
 * Configuration file for PHP CS Fixer.
 * Defines rules and files to scan.
 */

/**
 * Creates a Finder instance to locate PHP files within the bundle.
 * It includes files in the 'src' and 'tests' directories.
 *
 * @return PhpCsFixer\Finder
 */
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests');

/**
 * Creates the main Config instance for PHP CS Fixer.
 *
 * @return PhpCsFixer\Config
 */
$config = new PhpCsFixer\Config();

/**
 * Sets the rules to be applied.
 * Starts with the PSR-12 standard and adds/overrides specific rules.
 *
 * @param array<string, mixed> $rules An array of rules configuration.
 * @return $this The Config instance for method chaining.
 */
return $config->setRules([
    '@PSR12' => true, // Base standard
    'strict_param' => true, // Function parameters must have type declarations.
    'declare_strict_types' => true, // Force strict types declaration in all files.
    'array_syntax' => ['syntax' => 'short'], // Use short array syntax []
    'ordered_imports' => ['sort_algorithm' => 'alpha'], // Order 'use' statements alphabetically.
    'no_unused_imports' => true, // Remove unused 'use' statements.
    'single_quote' => true, // Convert double quotes to single quotes for simple strings.
    'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Add trailing comma in multiline arrays.
    'phpdoc_scalar' => true, // Scalar type hints should be used in PHPDoc.
    'phpdoc_trim' => true, // PHPDoc should be trimmed.
    'unary_operator_spaces' => true, // Ensure spaces around unary operators.
    'binary_operator_spaces' => [ // Ensure spaces around binary operators.
                                  'default' => 'single_space',
                                  'operators' => ['=>' => null] // Align '=>' in arrays if desired (null means default/ignore)
    ],
    'blank_line_before_statement' => [ // Ensure blank lines before certain statements.
                                       'statements' => ['return', 'throw', 'try', 'if', 'foreach', 'while']
    ],
    'not_operator_with_successor_space' => true, // Ensure space after '!' operator.
])
    /**
     * Sets the Finder instance to specify which files to process.
     *
     * @param \Traversable $finder The Finder instance.
     * @return $this The Config instance for method chaining.
     */
    ->setFinder($finder)
    /**
     * Enables the cache file for faster subsequent runs.
     *
     * @param bool $usingCache Whether to use the cache.
     * @return $this The Config instance for method chaining.
     */
    ->setUsingCache(true);