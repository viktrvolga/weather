<?php

declare(strict_types = 1);

namespace App\Weather\Provider\BBC;

final class XmlReader
{
    public static function wrap(\SimpleXMLElement $element): self
    {
        return new self($element);
    }

    public static function read(string $xmlString): self
    {
        $flag = \libxml_use_internal_errors(true);

        $xmlElement = \simplexml_load_string($xmlString);

        try
        {
            if($xmlElement !== false)
            {
                return new self($xmlElement);
            }

            throw new \RuntimeException(
                \sprintf('Unable to parse xml response: %s',
                    \implode(
                        ';',
                        \array_map(
                            static function(\LibXMLError $error): string
                            {
                                return $error->message;
                            }, \libxml_get_errors()
                        )
                    )
                )
            );
        }
        finally
        {
            \libxml_use_internal_errors($flag);
        }
    }

    public function readProperty(string $name): ?\SimpleXMLElement
    {
        return \property_exists($this->xmlElement, $name) ? $this->xmlElement->{$name} : null;
    }

    public function readPropertyAsString(string $name): ?string
    {
        $property = $this->readProperty($name);

        return $property !== null ? (string) $property : null;
    }

    private function __construct(private \SimpleXMLElement $xmlElement)
    {
    }
}