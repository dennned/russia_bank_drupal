<?php
namespace Drupal\russiabank\Controller;


/**
 * Provides route responses for the  module.
 */
class MainController {

    const XML_ALL_CURRENCIES = 'http://www.cbr.ru/scripts/XML_val.asp?d=0';
    const XML_DAILY_RATE = 'http://www.cbr.ru/scripts/XML_daily.asp';
    const XML_DINAMIC_RATE = 'http://www.cbr.ru/scripts/XML_dynamic.asp';

    const PATH_FILE = '/var/www/russia_bank_docker/export.json';


    /**
     * Returns a main content
     */
    public function contentAction() {
        return drupal_get_form('test_search_form');
    }

    /**
     * get xml data
     * @param string $url
     */
    public function getDailyData(string $url = '') : array
    {
        // get params
        $params = drupal_get_query_parameters();
        $paramsCurrencies = isset($params['currencies_options']) ? $params['currencies_options'] : null;
        $paramsFromDate = isset($params['date_from']) ? $params['date_from'] : null;
        $paramsToDate = isset($params['date_to']) ? $params['date_to'] : null;
        $paramsByPage = isset($params['by_page']) ? $params['by_page'] : null;
        $paramsExport = isset($params['export']) ? $params['export'] : null;

        $mode = false;
        if (isset($paramsFromDate) && !empty($paramsFromDate)) {
            $url = self::XML_DAILY_RATE.'?date_req='.$paramsFromDate;
            $mode = true;
        }

        if (isset($paramsToDate) && !empty($paramsToDate)) {
            $url = self::XML_DAILY_RATE.'?date_req='.$paramsToDate;
            $mode = true;
        }

        if (isset($paramsFromDate) && !empty($paramsFromDate) && isset($paramsToDate) && !empty($paramsToDate)) {
            $url = self::XML_DAILY_RATE.'?date_req1='.$paramsFromDate.'&date_req2='.$paramsToDate;
            $mode = true;
        }

        if (isset($paramsFromDate) && !empty($paramsFromDate) &&
            isset($paramsToDate) && !empty($paramsToDate) &&
            isset($paramsCurrencies) && !empty($paramsCurrencies)
        ) {
            $url = self::XML_DINAMIC_RATE.'?date_req1='.$paramsFromDate.'&date_req2='.$paramsToDate.'&VAL_NM_RQ='.$paramsCurrencies;
            $mode = true;
        }

        if (empty($url)) {
            $url = self::XML_DAILY_RATE.'?date_req='.date('d/m/yy');
        }

        $data = $this->getXMLData($url);
        if ($data['status'] === false) {
            return [];
        }

        $xmlData = $data['data'];

        // find currencies
        if (isset($paramsCurrencies) && !empty($paramsCurrencies)) {
            $xmlData = $xmlData->xpath('//*[@ID="'.$paramsCurrencies.'"]');
        }

        // export to json
        $fileToDownload = '';
        if (isset($paramsExport) && !empty($paramsExport)) {
            $json = json_encode($xmlData);

            // clear file
            file_put_contents(self::PATH_FILE, '');

            /// write to file
            file_put_contents(self::PATH_FILE, $json);

            if (is_file(self::PATH_FILE)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize(self::PATH_FILE));
                readfile(self::PATH_FILE);
            }
        }

        $resultData = [];
        if ($mode === false) {
            foreach ($xmlData as $val) {
                $resultData[] = [
                    'id' => $val->attributes()->ID->__toString(),
                    'numCode' => $val->children()->NumCode->__toString(),
                    'charCode' => $val->children()->CharCode->__toString(),
                    'nominal' => $val->children()->Nominal->__toString(),
                    'name' => $val->children()->Name->__toString(),
                    'value' => $val->children()->Value->__toString(),
                ];
            }
        } else {
            foreach ($xmlData as $val) {
                $resultData[] = [
                    'id' => $val->children()->attributes()->Id->__toString(),
                    'date' => $val->children()->attributes()->Date->__toString(),
                    'nominal' => $val->children()->children()->Nominal->__toString(),
                    'value' => $val->children()->children()->Value->__toString(),
                ];
            }
        }

        return ['data' => $resultData, 'mode' => $mode];
    }

    /**
     * @param string $url
     * @return array
     */
    public function getCurrencies(string $url = '') : array
    {
        if (empty($url)) {
            $url = self::XML_ALL_CURRENCIES;
        }

        $data = $this->getXMLData($url);

        if ($data['status'] === false) {
            return [];
        }

        $resultData = [0 => '---'];
        foreach ($data['data'] as $val) {
            $resultData[$val->attributes()->ID->__toString()] = $val->children()->Name->__toString();
        }

        return $resultData;
    }

    /**
     * @param string $url
     * @return array
     */
    private function getXMLData(string $url) : array
    {
        try {
            $context  = stream_context_create(['http' => ['header' => 'Accept: application/xml']]);
            $xmlContent = @file_get_contents($url, false, $context);
            $xmlData = simplexml_load_string($xmlContent);

            if ($xmlData instanceof \SimpleXMLElement) {
                $nodes = $xmlData->children();

                if (0 === $nodes->count()) {
                    return ['status' => false, 'data' => 'Empty result'];
                }

                return ['status' => true, 'data' => $nodes];
            }

            return ['status' => false, 'data' => 'Error xml data, reload the page'];

        } catch (\Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }
    }
}