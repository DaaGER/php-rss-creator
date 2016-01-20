<?php

/**
 * Created by PhpStorm.
 * User: daager
 * Date: 19.01.16
 * Time: 11:45
 */
class RssCreator {


    private $channel = [];
    private $xml     = [
        'version' => '1.0',
        'charset' => 'utf-8'
    ];
    private $rss     = [
        'version' => '2.0'
    ];

    private $items    = [];
    private $item_tmp = [];

    public function setXmlPar($key, $val) {
        $this->xml[$key] = $val;
    }

    public function setRssPar($key, $val) {
        $this->rss[$key] = $val;
    }

    public function getXml() {
        return $this->xml;
    }

    public function getRss() {
        return $this->rss;
    }

    public function setChannelPar($key, $val, $attr = []) {
        $this->channel[$key]['val'] = $val;
        $this->channel[$key]['attr'] = $attr;

        return $this;
    }

    public function channelTitle($val) {
        $this->setChannelPar('title', $val);

        return $this;
    }

    public function channelDesc($val) {
        $this->setChannelPar('description', $val);

        return $this;
    }

    public function channelLink($val) {
        $this->setChannelPar('link', $val);

        return $this;
    }

    /**
     * @param $val array ['url', 'title', 'link']
     *
     * @return $this
     */
    public function channelImage($val) {
        $this->setChannelPar('link', $val);

        return $this;
    }

    public function channelLastBuildDate($timestamp) {
        $this->setChannelPar('title', date(DATE_RSS, $timestamp));

        return $this;
    }


    public function setItemPar($key, $val, $attr = []) {
        str_replace(":encoded","",$key,$count);
        if($count>1){
            $val="<![CDATA[$val]]";
        }
        $this->item_tmp[$key]['val'] = $val;
        $this->item_tmp[$key]['attr'] = $attr;

        return $this;
    }

    public function itemTitle($val) {
        $this->setItemPar('title', $val);

        return $this;
    }

    public function itemDesc($val) {
        $this->setItemPar('description', $val);

        return $this;
    }

    public function itemLink($val,$guid=true) {
        $this->setItemPar('link', $val);
        if($guid){
            $this->setItemPar('guid',$val,['isPermaLink'=>'true']);
        }
        return $this;
    }

    public function itemLinkComments($val) {
        $this->setItemPar('comments', $val);

        return $this;
    }

    public function itemPubDate($timestamp) {
        $this->setItemPar('pubDate', date(DATE_RSS, $timestamp));

        return $this;
    }

    /**
     * @param $val array ['url','length bytes', 'mime']
     *
     * @return $this
     */
    public function itemEnclosure($val) {
        $this->setItemPar('enclosure', $val);

        return $this;
    }

    private function getItemTmp() {
        return $this->item_tmp;
    }

    private function getItem($key) {
        return $this->items[$key];
    }

    public function addItem($key = '') {
        if (empty($key)) {
            $this->items[] = $this->getItemTmp();
        } else {
            $this->items[$key] = $this->getItemTmp();
        }

    }

    public function sendHeader(){
        header('Content-Type: application/rss+xml; charset='.$this->getXml()['charset']);
    }

    public function generateRss() {
        $xml_par = $this->getXml();
        $xml_version = $xml_par['version'];
        $xml_charset = $xml_par['charset'];

        $xml = new domDocument($xml_version, $xml_charset);
        $rss = $xml->createElement('rss');

        //rss
        foreach ($this->getRss() as $key => $value) {
            $rss->setAttribute($key, $value);
        }
        $xml->appendChild($rss);

        //channel
        $channel = $xml->createElement('channel');
        foreach ($this->channel as $key => $value) {
            $row = $xml->createElement($key, $value['val']);
            if(!empty($value['attr'])){
                foreach($value['attr'] as $attr_key=>$attr_val) {
                    $row->setAttribute($attr_key,$attr_val);
                }
            }
            $channel->appendChild($row);
        }
        $rss->appendChild($channel);
        foreach ($this->items as $item_key => $item) {
            $item_element = $xml->createElement('item');
            foreach ($item as $key => $value) {
                $row = $xml->createElement($key, $value['val']);
                if(!empty($value['attr'])){
                    foreach($value['attr'] as $attr_key=>$attr_val) {
                        $row->setAttribute($attr_key,$attr_val);
                        }
                }
                $item_element->appendChild($row);
            }
            $channel->appendChild($item_element);
        }


        return $xml->saveXML();
    }
}
