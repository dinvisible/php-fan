<?php

declare(strict_types=1);

namespace fan\project\block\root;
/**
 * html 5 root template block
 * @version of file: 05.02.005 (12.02.2015)
 */
class html5 extends \fan\core\block\root\html
{
    public function setMetaByDb($idMetaData): static
    {
        $metaMain = gr('mysql\ad_seo\meta_data_main', $idMetaData);
        if ($metaMain->checkIsLoad()){
            // Main meta-data
            $title = $metaMain->getByLocal('title');
            if (!empty($title)) {
                $this->setTitle($title);
            }
            foreach (['description', 'keywords'] as $k) {
                $cont = $metaMain->getByLocal($k);
                if (!empty($cont)) {
                    $this->setMetaTag([
                        'name'    => $k,
                        'content' => $cont
                    ]);
                }
            }

            // OG meta-data
            $metaOg = ge('mysql\ad_seo\meta_data_og')->getRowsetByParam(['id_meta_data_main' => $metaMain->getId()]);
            if (count($metaOg) > 0){
                $idSiteLang = $this->containerService('locale')->getLanguageId();
                foreach ($metaOg as $v) {
                    $lngId = $v->get_id_site_language();
                    if (is_null($lngId) || (string)$idSiteLang === (string)$lngId){
                        $this->setMetaTag([
                            'property' => $v->get_key(),
                            'content'  => $v->get_value(),
                        ]);
                    }
                }
            }
        }
        return $this;
    }

}
