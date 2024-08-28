<?php

namespace App\Trait;

trait Serializable
{
    public function toArray(): array
    {
        return $this->convertToArray($this);
    }

    public function toJson(): string
    {
        return json_encode($this->convertToArray($this));
    }

    private function convertToArray(mixed $unknown, $isFirstIteration = true): mixed
    {
        if($unknown instanceof \UnitEnum) {
            return $unknown->value;
        }

        if (is_array($unknown)) {
            return array_map(function ($item) {
                return $this->convertToArray($item, false);
            }, $unknown);
        }

        if (is_object($unknown)) {
            if (!$isFirstIteration && $this->isSerializable($unknown)) {
                return $unknown->toArray();
            }

            $array = [];
            foreach (get_object_vars($unknown) as $key => $value) {
                if(str_starts_with($key, "__")) {
                    continue; //discarding private key
                }

                if($value instanceof \UnitEnum) {
                    $array[$key] = $value->value;
                    continue;
                }

                if (is_array($value)) {
                    $array[$key] = array_map(function ($item) {
                        return $this->convertToArray($item, false);
                    }, $value);
                    continue;
                }

                if (is_object($value)) {
                    if ($this->isSerializable($value)) {
                        $array[$key] = $value->toArray();
                        continue;
                    }
                    $array[$key] = get_object_vars($value);
                    continue;
                }

                $array[$key] = $value;
            }
            return $array;
        }

        return $unknown;
    }

    private function isSerializable(object $object)
    {
        return array_key_exists(Serializable::class, class_uses($object));
    }
}
