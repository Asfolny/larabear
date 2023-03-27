<?php declare(strict_types=1);

namespace GuardsmanPanda\Larabear\Infrastructure\App\Service;

use GuardsmanPanda\Larabear\Infrastructure\Http\Service\Resp;

final class BearArrayService {
    /**
     * @param array<mixed> $array
     * @param string $key
     * @return array<int|string, array<int, mixed>>.
     */
    public static function groupArrayBy(array $array, string $key): array {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $result[$item[$key]][] = $item;
            } else {
                $result[$item->$key][] = $item;
            }
        }
        return $result;
    }
}
