<?php
namespace Araneum\Base\Utils;

/**
 * Class StatisticsHelper
 *
 * @package Araneum\Base\Utils
 */
class StatisticsHelper
{
    /**
     *  Get array
     *
     * @param  array $pack
     * @param  mixed $column
     * @return array
     */
    public static function getResultByColumnName(array $pack, $column)
    {
        return array_values(array_column($pack, $column));
    }

    /**
     * Prepare result for UpTime chart
     *
     * @param array $dataArray
     * @param array $statuses
     * @return array
     */
    public static function prepareResultForUpTime($dataArray, $statuses)
    {
        $chartArray = [];
        $result = [];
        foreach ($statuses as $status) {
            $chartArray[$status] = [
                'label' => $status,
                'data' => [],
            ];
        }

        foreach ($dataArray as $array) {
            foreach ($statuses as $status) {
                array_push(
                    $chartArray[$status]['data'],
                    [
                        $array['name'],
                        $array[$status],
                    ]
                );
            }
        }

        foreach ($chartArray as $array) {
            array_push($result, $array);
        }

        return $result;
    }

    /**
     * Init chart structure
     *
     * @param  array  $hours
     * @param  array  $list
     * @param  string $countField
     * @return array
     */
    public static function getChartStructure(array $hours, array $list, $countField = 'cnt')
    {
        $data = [];
        foreach ($list as $item) {
            if (!isset($data[$item['name']])) {
                $data[$item['name']] = [
                    'label' => $item['name'],
                    'data' => $hours,
                ];
            }

            if (is_null($item['hours'])) {
                continue;
            }

            $key = $item['hours'];

            $data[$item['name']]['data'][$key] = round($item[$countField]);
            if ($countField == 'apt') {
                $data[$item['name']]['data'][$key] = round($item[$countField]);
            }
        }

        $data = array_values($data);

        foreach ($data as &$item) {
            $token = [];

            $flagKey = null;

            foreach ($item['data'] as $key => $val) {
                if (!is_string($key) && $key <= 9) {
                    $key = '0'.$key;
                }

                if ($key == '24') {
                    $flagKey = count($token);
                }

                $token[] = [
                    (string) $key,
                    $val,
                ];
            }

            if (!empty($flagKey) && is_array($token[count($token) - 1]) && $token[count($token) - 1][0] == '00') {
                $token[$flagKey] = array_pop($token);
            }

            $item['data'] = $token;
        }

        return array_values($data);
    }
}
