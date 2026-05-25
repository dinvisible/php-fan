<?php

declare(strict_types=1);

/**
 * Imports fully-qualified class names used in PHP code into top-level use
 * declarations. String literals and comments are intentionally untouched.
 */
final class fully_qualified_name_importer
{
    private const TARGET_DIRS = ['_core', '_project', 'htdocs', 'unit', 'tools'];

    private bool $dryRun;
    private bool $fromHead;
    /** @var array<string,true> */
    private array $knownClasses = [];

    public function __construct(array $arguments)
    {
        $this->dryRun = in_array('--dry-run', $arguments, true);
        $this->fromHead = in_array('--from-head', $arguments, true);
    }

    public function run(): int
    {
        $changed = 0;
        $skipped = 0;
        $this->knownClasses = $this->knownClasses();

        foreach ($this->phpFiles() as $file) {
            $source = $this->fromHead ? $this->sourceFromHead($file) : file_get_contents($file);
            if (!is_string($source)) {
                $skipped++;
                continue;
            }

            $result = $this->rewrite($source);
            if ($result === null) {
                $skipped++;
                continue;
            }

            if ($result !== $source) {
                $changed++;
                if (!$this->dryRun) {
                    file_put_contents($file, $result);
                }
                echo ($this->dryRun ? 'would update ' : 'updated ') . $file . PHP_EOL;
            }
        }

        echo sprintf(
            "%s files: %d changed, %d skipped\n",
            $this->dryRun ? 'Dry-run' : 'Updated',
            $changed,
            $skipped
        );

        return 0;
    }

    private function sourceFromHead(string $file): ?string
    {
        $command = 'git show ' . escapeshellarg('HEAD:' . $file) . ' 2>/dev/null';
        $source = shell_exec($command);
        if (!is_string($source)) {
            return file_get_contents($file) ?: null;
        }

        return $source;
    }

    /**
     * @return list<string>
     */
    private function phpFiles(): array
    {
        $files = [];
        foreach (self::TARGET_DIRS as $dir) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
        sort($files);

        return $files;
    }

    private function rewrite(string $source): ?string
    {
        $tokens = token_get_all($source, TOKEN_PARSE);
        $namespaceCount = $this->countNamespaceDeclarations($tokens);
        if ($namespaceCount > 1 || $this->hasBracketedNamespace($tokens)) {
            return null;
        }

        $existingUses = $this->existingUses($tokens);
        $declaredNames = $this->declaredNames($tokens);
        $namespaceName = $this->namespaceName($tokens);
        $candidates = $this->fullyQualifiedNames($tokens);
        if ($candidates === []) {
            return $source;
        }

        $imports = [];
        $aliases = [];
        $occupiedAliases = [];
        foreach ($existingUses as $alias => $name) {
            $occupiedAliases[strtolower($alias)] = strtolower($name);
        }
        foreach ($declaredNames as $name) {
            $occupiedAliases[strtolower($name)] = '__declared__';
        }

        foreach (array_unique($candidates) as $name) {
            $name = ltrim($name, '\\');
            if (str_contains($name, '\\') === false) {
                continue;
            }

            $short = $this->shortName($name);
            $aliasKey = strtolower($short);
            if ($namespaceName !== null) {
                $currentNamespaceName = strtolower($namespaceName . '\\' . $short);
                if (isset($this->knownClasses[$currentNamespaceName]) && $currentNamespaceName !== strtolower($name)) {
                    $occupiedAliases[$aliasKey] ??= '__current_namespace__';
                }
            }
            if (($occupiedAliases[$aliasKey] ?? null) === strtolower($name)) {
                $aliases[$name] = $short;
                continue;
            }

            if (!isset($occupiedAliases[$aliasKey])) {
                $occupiedAliases[$aliasKey] = strtolower($name);
                $aliases[$name] = $short;
                if (!isset($existingUses[$short])) {
                    $imports[$name] = null;
                }
                continue;
            }

            $alias = $this->uniqueAlias($name, $occupiedAliases);
            $occupiedAliases[strtolower($alias)] = strtolower($name);
            $aliases[$name] = $alias;
            $imports[$name] = $alias;
        }

        if ($aliases === []) {
            return $source;
        }

        if ($imports !== []) {
            $source = $this->insertImports($source, $tokens, $imports);
        }

        return $this->replaceNames(token_get_all($source, TOKEN_PARSE), $aliases);
    }

    /**
     * @return array<string,true>
     */
    private function knownClasses(): array
    {
        $classes = [];
        foreach ($this->phpFiles() as $file) {
            $source = $this->fromHead ? $this->sourceFromHead($file) : file_get_contents($file);
            if (!is_string($source)) {
                continue;
            }
            $tokens = token_get_all($source, TOKEN_PARSE);
            $namespace = $this->namespaceName($tokens);
            foreach ($this->declaredNames($tokens) as $name) {
                $classes[strtolower($namespace === null ? $name : $namespace . '\\' . $name)] = true;
            }
        }

        return $classes;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function namespaceName(array $tokens): ?string
    {
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_NAMESPACE) {
                continue;
            }
            $name = '';
            for ($j = $i + 1; $j < $count; $j++) {
                if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                    return $name === '' ? null : $name;
                }
                if ($this->isIgnorable($tokens[$j])) {
                    continue;
                }
                $name .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
            }
        }

        return null;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function countNamespaceDeclarations(array $tokens): int
    {
        $count = 0;
        foreach ($tokens as $index => $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function hasBracketedNamespace(array $tokens): bool
    {
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_NAMESPACE) {
                continue;
            }
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->isIgnorable($tokens[$j])) {
                    continue;
                }
                if ($tokens[$j] === '{') {
                    return true;
                }
                if ($tokens[$j] === ';') {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @return array<string,string>
     */
    private function existingUses(array $tokens): array
    {
        $uses = [];
        $count = count($tokens);
        $depth = 0;
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token === '{') {
                $depth++;
                continue;
            }
            if ($token === '}') {
                $depth--;
                continue;
            }
            if ($depth !== 0 || !is_array($token) || $token[0] !== T_USE) {
                continue;
            }
            $previous = $this->previousMeaningful($tokens, $i);
            if ($previous !== null && $tokens[$previous] === ')') {
                continue;
            }

            $statement = '';
            for ($j = $i + 1; $j < $count && $tokens[$j] !== ';'; $j++) {
                $statement .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
            }
            if (preg_match('/^\s*(?:function|const)\b/i', $statement)) {
                continue;
            }
            if (str_contains($statement, ',')) {
                continue;
            }
            if (!preg_match('/^\s*\\\\?([A-Za-z_][A-Za-z0-9_\\\\]*)(?:\s+as\s+([A-Za-z_][A-Za-z0-9_]*))?\s*$/i', $statement, $matches)) {
                continue;
            }
            $name = ltrim($matches[1], '\\');
            $alias = $matches[2] ?? $this->shortName($name);
            $uses[$alias] = $name;
        }

        return $uses;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @return list<string>
     */
    private function declaredNames(array $tokens): array
    {
        $names = [];
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || !in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
                continue;
            }
            $previous = $this->previousMeaningful($tokens, $i);
            if ($previous !== null && is_array($tokens[$previous]) && $tokens[$previous][0] === T_NEW) {
                continue;
            }
            $next = $this->nextMeaningful($tokens, $i);
            if ($next !== null && is_array($tokens[$next]) && $tokens[$next][0] === T_STRING) {
                $names[] = $tokens[$next][1];
            }
        }

        if (defined('T_ENUM')) {
            for ($i = 0; $i < $count; $i++) {
                if (is_array($tokens[$i]) && $tokens[$i][0] === T_ENUM) {
                    $next = $this->nextMeaningful($tokens, $i);
                    if ($next !== null && is_array($tokens[$next]) && $tokens[$next][0] === T_STRING) {
                        $names[] = $tokens[$next][1];
                    }
                }
            }
        }

        return $names;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @return list<string>
     */
    private function fullyQualifiedNames(array $tokens): array
    {
        $names = [];
        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_NAME_FULLY_QUALIFIED) {
                continue;
            }
            $name = ltrim($token[1], '\\');
            if (!str_contains($name, '\\')) {
                continue;
            }
            if ($this->isFunctionCall($tokens, $index)) {
                continue;
            }
            $names[] = $name;
        }

        return $names;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function isFunctionCall(array $tokens, int $index): bool
    {
        $next = $this->nextMeaningful($tokens, $index);
        if ($next === null || $tokens[$next] !== '(') {
            return false;
        }

        $previous = $this->previousMeaningful($tokens, $index);
        if ($previous === null) {
            return true;
        }

        $previousToken = $tokens[$previous];
        if (is_array($previousToken) && in_array($previousToken[0], [T_NEW, T_INSTANCEOF], true)) {
            return false;
        }

        return true;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @param array<string,string> $aliases
     */
    private function replaceNames(array $tokens, array $aliases): string
    {
        $source = '';
        foreach ($tokens as $index => $token) {
            if (is_array($token) && $token[0] === T_NAME_FULLY_QUALIFIED && !$this->isInsideTopLevelUse($tokens, $index)) {
                $name = ltrim($token[1], '\\');
                $source .= $aliases[$name] ?? $token[1];
                continue;
            }
            $source .= is_array($token) ? $token[1] : $token;
        }

        return $source;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function isInsideTopLevelUse(array $tokens, int $index): bool
    {
        $depth = 0;
        for ($i = 0; $i < $index; $i++) {
            if ($tokens[$i] === '{') {
                $depth++;
            } elseif ($tokens[$i] === '}') {
                $depth--;
            }
        }

        if ($depth !== 0) {
            return false;
        }

        for ($i = $index - 1; $i >= 0; $i--) {
            if ($tokens[$i] === ';' || $tokens[$i] === '{' || $tokens[$i] === '}') {
                return false;
            }
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_USE) {
                $previous = $this->previousMeaningful($tokens, $i);

                return $previous === null || $tokens[$previous] !== ')';
            }
        }

        return false;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @param array<string, string|null> $imports
     */
    private function insertImports(string $source, array $tokens, array $imports): string
    {
        ksort($imports, SORT_NATURAL | SORT_FLAG_CASE);
        $statements = [];
        foreach ($imports as $name => $alias) {
            $statements[] = 'use ' . $name . ($alias === null ? '' : ' as ' . $alias) . ';';
        }
        $block = implode("\n", $statements) . "\n";

        $offset = $this->importInsertionOffset($source, $tokens);
        if ($offset === null) {
            return $source;
        }

        $prefix = substr($source, 0, $offset);
        $suffix = substr($source, $offset);
        $separator = str_ends_with($prefix, "\n\n") ? '' : "\n";

        return $prefix . $separator . $block . $suffix;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function importInsertionOffset(string $source, array $tokens): ?int
    {
        $count = count($tokens);
        $lastUseEnd = null;
        $namespaceEnd = null;
        $declareEnd = null;
        $offset = 0;
        $depth = 0;

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            if ($token === '{') {
                $depth++;
            } elseif ($token === '}') {
                $depth--;
            }

            if ($depth === 0 && is_array($token) && $token[0] === T_DECLARE) {
                $declareEnd = $this->statementEndOffset($tokens, $i, $offset);
            }
            if ($depth === 0 && is_array($token) && $token[0] === T_NAMESPACE) {
                $namespaceEnd = $this->statementEndOffset($tokens, $i, $offset);
            }
            if ($depth === 0 && is_array($token) && $token[0] === T_USE) {
                $previous = $this->previousMeaningful($tokens, $i);
                if ($previous === null || $tokens[$previous] !== ')') {
                    $lastUseEnd = $this->statementEndOffset($tokens, $i, $offset);
                }
            }

            $offset += strlen($text);
        }

        return $lastUseEnd ?? $namespaceEnd ?? $declareEnd ?? $this->afterOpenTagOffset($source);
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function statementEndOffset(array $tokens, int $start, int $startOffset): ?int
    {
        $offset = $startOffset;
        $count = count($tokens);
        for ($i = $start; $i < $count; $i++) {
            $text = is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
            $offset += strlen($text);
            if ($tokens[$i] === ';') {
                return $offset;
            }
        }

        return null;
    }

    private function afterOpenTagOffset(string $source): int
    {
        if (preg_match('/^<\?php[ \t]*\r?\n/', $source, $matches)) {
            return strlen($matches[0]);
        }

        return 0;
    }

    /**
     * @param array<string,string> $occupiedAliases
     */
    private function uniqueAlias(string $name, array $occupiedAliases): string
    {
        $parts = explode('\\', $name);
        for ($length = 2; $length <= count($parts); $length++) {
            $alias = implode('_', array_slice($parts, -$length));
            if (!isset($occupiedAliases[strtolower($alias)])) {
                return $alias;
            }
        }

        $alias = $this->shortName($name);
        $suffix = 2;
        while (isset($occupiedAliases[strtolower($alias . '_' . $suffix)])) {
            $suffix++;
        }

        return $alias . '_' . $suffix;
    }

    private function shortName(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? $name : substr($name, $position + 1);
    }

    /**
     * @param array{0:int,1:string,2:int}|string $token
     */
    private function isIgnorable(array|string $token): bool
    {
        return is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function previousMeaningful(array $tokens, int $index): ?int
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if (!$this->isIgnorable($tokens[$i])) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private function nextMeaningful(array $tokens, int $index): ?int
    {
        $count = count($tokens);
        for ($i = $index + 1; $i < $count; $i++) {
            if (!$this->isIgnorable($tokens[$i])) {
                return $i;
            }
        }

        return null;
    }
}

exit((new fully_qualified_name_importer($argv))->run());
