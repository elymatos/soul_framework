<?php

namespace App\Services;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Illuminate\Support\Facades\Storage;

class YamlParserService
{
    public function parseFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("YAML file not found: {$filePath}");
        }

        try {
            $content = file_get_contents($filePath);
            return Yaml::parse($content);
        } catch (ParseException $e) {
            throw new \RuntimeException("Failed to parse YAML file: " . $e->getMessage(), 0, $e);
        }
    }

    public function parseFromStorage(string $path): array
    {
        if (!Storage::exists($path)) {
            throw new \InvalidArgumentException("YAML file not found in storage: {$path}");
        }

        try {
            $content = Storage::get($path);
            return Yaml::parse($content);
        } catch (ParseException $e) {
            throw new \RuntimeException("Failed to parse YAML from storage: " . $e->getMessage(), 0, $e);
        }
    }

    public function parseString(string $yamlContent): array
    {
        try {
            return Yaml::parse($yamlContent);
        } catch (ParseException $e) {
            throw new \RuntimeException("Failed to parse YAML string: " . $e->getMessage(), 0, $e);
        }
    }

    public function toYaml(array $data, int $inline = 2, int $indent = 4): string
    {
        return Yaml::dump($data, $inline, $indent);
    }

    public function saveToFile(array $data, string $filePath, int $inline = 2, int $indent = 4): bool
    {
        try {
            $yamlContent = $this->toYaml($data, $inline, $indent);
            return file_put_contents($filePath, $yamlContent) !== false;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to save YAML to file: " . $e->getMessage(), 0, $e);
        }
    }

    public function saveToStorage(array $data, string $path, int $inline = 2, int $indent = 4): bool
    {
        try {
            $yamlContent = $this->toYaml($data, $inline, $indent);
            return Storage::put($path, $yamlContent);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to save YAML to storage: " . $e->getMessage(), 0, $e);
        }
    }
}