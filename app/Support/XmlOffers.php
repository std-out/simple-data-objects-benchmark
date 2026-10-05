<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use Generator;
use SimpleXMLElement;
use XMLReader;

final class XmlOffers
{
    public const string PATH = 'catalog/shop/offers/offer';

    /**
     * What a consumer has to hand-write without XML support in the DTO
     * library: turn one <offer> node into the array shape the DTO expects.
     *
     * @return array<string, mixed>
     */
    public static function toArray(SimpleXMLElement $offer): array
    {
        $params = [];

        foreach ($offer->param as $param) {
            $params[] = ['name' => (string) $param['name'], 'value' => (string) $param];
        }

        $pictures = [];

        foreach ($offer->picture as $picture) {
            $pictures[] = (string) $picture;
        }

        return [
            'id' => (int) $offer['id'],
            'available' => (string) $offer['available'] === 'true',
            'name' => (string) $offer->name,
            'price' => ['amount' => (float) $offer->price, 'currency' => (string) $offer->price['currency']],
            'pictures' => $pictures,
            'params' => $params,
            'stock' => isset($offer->stock) ? (int) $offer->stock : null,
            'vendor' => isset($offer->vendor)
                ? ['code' => (string) $offer->vendor['code'], 'name' => (string) $offer->vendor->name]
                : null,
        ];
    }

    /**
     * Hand-rolled streaming: XMLReader, one expanded node at a time.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public static function stream(string $path): Generator
    {
        $xml = XMLReader::fromUri($path);
        $document = new DOMDocument;

        try {
            while ($xml->read() && $xml->name !== 'offer') {
            }

            while ($xml->name === 'offer') {
                yield self::toArray(simplexml_import_dom($document->importNode($xml->expand(), true)));

                $xml->next('offer');
            }
        } finally {
            $xml->close();
        }
    }

    public static function writeSample(string $path, int $nodes): void
    {
        $directory = dirname($path);
        is_dir($directory) || mkdir($directory, 0777, true);
        $handle = fopen($path, 'wb');
        fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?><catalog><shop><name>Benchmark</name><offers>');

        for ($i = 1; $i <= $nodes; $i++) {
            fwrite($handle, <<<XML
                <offer id="{$i}" available="true">
                    <name>Electric kettle {$i}</name>
                    <price currency="UAH">499.90</price>
                    <stock>12</stock>
                    <picture>https://example.com/images/{$i}-front.jpg</picture>
                    <picture>https://example.com/images/{$i}-side.jpg</picture>
                    <param name="Color">Red</param>
                    <param name="Volume">1.7 L</param>
                    <param name="Power">2200 W</param>
                    <vendor code="BSH"><name>Bosch</name></vendor>
                    <description>Stainless steel body, auto shut-off, boil-dry protection, 360 degree base.</description>
                </offer>

                XML);
        }

        fwrite($handle, '</offers></shop></catalog>');
        fclose($handle);
    }
}
