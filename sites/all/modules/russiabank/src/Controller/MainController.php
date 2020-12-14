<?php
namespace Drupal\russiabank\Controller;


/**
 * Provides route responses for the  module.
 */
class MainController {

    const XML_ALL_CURRENCIES = 'http://www.cbr.ru/scripts/XML_val.asp?d=0';
    const XML_DAILY_RATE = 'http://www.cbr.ru/scripts/XML_daily.asp';
    const XML_DINAMIC_RATE = 'http://www.cbr.ru/scripts/XML_dynamic.asp';

    const PATH_FILE = '/var/www/russia_bank/public/';


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
        if (empty($url)) {
            $url = self::XML_DAILY_RATE.'?date_req='.date('d/m/yy');
        }

        $data = $this->getXMLData($url);
        if ($data['status'] === false) {
            return $data;
        }

        $xmlData = $data['data'];

        $resultData = [];
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

        return $resultData;
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
            $resultData[] = [
                'name' => $val->children()->Name->__toString(),
                'id' => $val->attributes()->ID->__toString(),
            ];
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