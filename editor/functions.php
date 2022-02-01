<?php 


class Exporters
{

    static function alephseq($r)
    {

        $author_number = count($r['author']);
                                        
        $record = [];
        $record[] = "000000001 FMT   L BK";
        $record[] = "000000001 LDR   L ^^^^^nab^^22^^^^^Ia^4500";
        $record[] = '000000001 BAS   L $$a04';
        $record[] = '000000001 008   L ^^^^^^s'.((isset($r["datePublished"])? $r["datePublished"] : '^^^^')).'^^^^^^^^^^^^^^^^^^000^0^^^^^d';
        if (isset($r['doi'])) {
            $record[] = '000000001 0247  L $$a'.$r["doi"].'$$2DOI';         
        } else {
            $record[] = '000000001 0247  L $$a$$2DOI';
        }
        $record[] = '000000001 040   L $$aUSP/SIBI';
        $record[] = '000000001 0410  L $$a';
        $record[] = '000000001 044   L $$a'.$r["country"].'';


        if (isset($r["author_0_person_name"])) {
            $record[] = '000000001 1001  L $$a'.$r["author_0_person_name"].'$$d$$0'.((isset($r["author_0_person_identifier_value"])? $r["author_0_person_identifier_value"] : '')).'$$1$$4$$5'.((isset($r["author_0_organization_external"])? $r["author_0_organization_external"] : '')).'$$7$$8'.((isset($r["author_0_organization_name"])? $r["author_0_organization_name"] : '')).'$$9';
        }         
        
        $record[] = '000000001 2451'.$r["ignoreCharacters"].' L $$a'.$r["name"].'$b'.((isset($r["subtitle"])? $r["subtitle"] : '')).'';                                            
        if (isset($r["trabalhoEmEventos"])) {  
            $record[] = '000000001 260   L $$a'.((isset($r["trabalhoEmEventos"]["cidadeDaEditora"]) && $r["trabalhoEmEventos"]["cidadeDaEditora"])? $r["trabalhoEmEventos"]["cidadeDaEditora"] : '').'$$b'.((isset($r["trabalhoEmEventos"]["nomeDaEditora"]) && $r["trabalhoEmEventos"]["nomeDaEditora"])? $r["trabalhoEmEventos"]["nomeDaEditora"] : '').'$$c'.$r["datePublished"].'';
        } else {
            $record[] = '000000001 260   L $$a$$b'.((isset($r["publisher_organization_name"])? $r["publisher_organization_name"] : '')).'$$c'.$r["datePublished"].'';
        }        
        $record[] = '000000001 300   L $$ap. '.((isset($r["isPartOf_pageStart"])?$r["isPartOf_pageStart"]:"")).'-'.((isset($r["isPartOf_pageEnd"])?$r["isPartOf_pageEnd"]:"")).'';

        if (isset($r['doi'])) {
            $record[] = '000000001 500   L $$aDisponível em: https://doi.org/'.$r["doi"].' . Acesso em: '.date('d M Y').'';
        } else {
            $record[] = '000000001 500   L $$a';
        }

        if (isset($r["artigoPublicado"])) {
            $record[] = '000000001 5101  L $$aIndexado no:';
        }
        
        $i_funder = 0;
        do {
            $key_organization_name =  'funder_'.$i_funder.'_organization_name';
            $key_organization_projectNumber =  'funder_'.$i_funder.'_organization_projectNumber';
            if (isset($r[$key_organization_name])) {
                $record[] = '000000001 536   L $$a'.$r[$key_organization_name].'$$f'.((isset($r[$key_organization_projectNumber])? $r[$key_organization_projectNumber] : '')).'';
            }
            $i_funder++;
        } while ($i_funder < 100);


        $record[] = '000000001 650 7 L $$a';
        $record[] = '000000001 650 7 L $$a';
        $record[] = '000000001 650 7 L $$a';
        $record[] = '000000001 650 7 L $$a';
        $record[] = '000000001 650 7 L $$a';

        $i = 1;
        do {
            $key =  'author_'.$i.'_person_name';
            $key_person_identifier_value =  'author_'.$i.'_person_identifier_value';
            $key_organization_name =  'author_'.$i.'_organization_name';
            $key_organization_external =  'author_'.$i.'_organization_external';

            if (isset($r[$key])) {
                $record[] = '000000001 7001  L $$a'.$r[$key].'$$d$$0'.((isset($r[$key_person_identifier_value])? $r[$key_person_identifier_value] : '')).'$$1$$4$$5'.((isset($r[$key_organization_external])? $r[$key_organization_external] : '')).'$$7$$8'.((isset($r[$key_organization_name])? $r[$key_organization_name] : '')).'$$9';
            }
            $i++;
        } while ($i < 100);
     
        if (isset($r["isPartOf_name"])) {
            $record[] = '000000001 7730  L $$t'.$r["isPartOf_name"].'$$x'.((isset($r["isPartOf_ISSN"])? $r["isPartOf_ISSN"] : '')).'$$hv.'.((isset($r["isPartOf_volume"])? $r["isPartOf_volume"] : '')).', n. '.((isset($r["isPartOf_issue"])? $r["isPartOf_issue"] : '')).', p.'.((isset($r["isPartOf_pageStart"])? $r["isPartOf_pageStart"] : '')).'-'.((isset($r["isPartOf_pageEnd"])? $r["isPartOf_pageEnd"] : '')).', '.$r["datePublished"].'';
        }                                            
        
        
        if (isset($r['doi'])) {                                            
            $record[] = '000000001 8564  L $$zClicar sobre o botão para acesso ao texto completo$$uhttps://doi.org/'.$r["doi"].'$$3DOI';           
        } else {
            $record[] = '000000001 8564  L $$zClicar sobre o botão para acesso ao texto completo$$u$$3DOI';
        }
        
        if (!empty($r['url'])) {                                            
            $record[] = '000000001 8564  L $$zClicar sobre o botão para acesso ao texto completo$$u'.$r["url"].'$$3Documento completo';           
        }

        if (isset($r["isPartOf_name"])) {
            $record[] = '000000001 945   L $$aP$$bARTIGO DE PERIODICO$$c01$$j'.$r["datePublished"].'$$l';
        }                                            
        $record[] = '000000001 946   L $$a';
        
        $i_about = 0;
        do {
            $key_about =  'about_'.$i_about.'';
            if (isset($r[$key_about])) {
                $record[] = '000000001 952   L $$a'.$r[$key_about].'';
            }
            $i_about++;
        } while ($i_about < 100);          

        
        //sort($record);

        $record_blob = implode("\\n", $record);

        return $record_blob;

    }    

}

Class Lookup {
    
    static function article($title) {
        $articles = ["the", "an", "a", "le", "la", "l’", "les", "un", "une", "a", "des", "der", "die", "das", "ein", "eine", "il",   "la", "lo", "i", "gli", "le", "uno", "una", "un’", "o", "a", "os", "as", "um", "uma", "uns", "umas", "el", "la", "le", "lo", "las", "los", "unos", "unas"];
        $titleArray = explode(" ", $title);
        if (in_array(strtolower($titleArray[0]), $articles)) {
            $charactersToIgnore = strlen($titleArray[0]) + 1;            
        } else {
            $charactersToIgnore = 0;
        }
        return $charactersToIgnore;
    }

} 




?>