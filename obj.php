<?php
if (isset($_GET) || isset($_POST))
{
    $request = isset($_GET) ? $_GET : $_POST;
    $obj = isset($request['obj']) ? $request['obj'] : false;

    $obj = explode(";", $obj);

    for ($i = 0;$i < count($obj);$i++)
    {
        $post = array(
            'Objetos' => $obj[$i]
        );
        $ch = curl_init();
        $url = "https://www.linkcorreios.com.br/?id=$obj[$i]";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $output = curl_exec($ch);
        curl_close($ch);

        $out = explode("<ul class=\"linha_status\" style=\"\">", $output);

        if (isset($out[1]))
        {
            $output = explode("<ul class=\"linha_status\" style=\"\">", $output);
            $k = count($output) - 1;
            $output[$k] = explode("</ul>", $output[$k]);
            $output[$k] = $output[$k][0];
            $output[0] = null;

            $output = str_replace("<b>", "", $output);
            $output = str_replace("</b>", "", $output);
            $output = str_replace("Status:", "", $output);
            $output = str_replace("Data  : ", "", $output);
            $output = str_replace("Hora:", "", $output);
            $output = str_replace("Local:", "", $output);
            $output = str_replace("Origem:", "", $output);
            $output = str_replace("Destino:", "", $output);

            for ($n = 1;$n <= 6;$n++)
            {

                $info = explode("<li>", $output[$n]);
                $hd = explode("|", $info[2]);

                $dados[$n] = array(
                    'Status' => strip_tags(trim(@$info[1])) ,
                    'Dia' => strip_tags(trim(@$hd[0])) ,
                    'Hora' => strip_tags(trim(@$hd[1])) ,
                    'Local' => strip_tags(trim(@$info[3])) ,
                    'Origem' => strip_tags(trim(@$info[3])) ,
                    'Destino' => strip_tags(trim(@$info[4])) ,
                    'Update' => ''
                );

                if (count($info) >= 4)
                {
                    $dados[$n]['Origem'] = '';
                }

                if ("" != $dados[$n]['Dia'])
                {
                    $exploDate = explode('/', $dados[$n]['Dia']);
                    $dia1 = $exploDate[2] . '-' . $exploDate[1] . '-' . $exploDate[0];
                    $dia2 = date('Y-m-d');

                    $diferenca = strtotime($dia2) - strtotime($dia1);
                    $dias = (floor($diferenca / (60 * 60 * 24))) + 1;

                    $dados[$n]['Update'] = $change = "há {$dias} dias";
                }
            }
        }
        else
        {
            $jsonObcject = new stdClass();
            $jsonObcject->erro = true;
            $jsonObcject->msg = "Objeto não encontrado";
            $jsonObcject->obj = $obj[$i];
            header('Content-type: application/json');
            $json = json_encode($jsonObcject);
            print_r($json);
            exit;
        }

        $arrayCompleto[$obj[$i]] = (object)$dados;

    }

    $jsonObcject = (object)$arrayCompleto;
    header('Content-type: application/json');
    $json = json_encode($jsonObcject, JSON_UNESCAPED_UNICODE);
    //print_r($json);
    echo $json->Status."<br>";
}
else
{
    $jsonObcject = new stdClass();
    $jsonObcject->erro = true;
    $jsonObcject->msg = "Objeto não encontrado";
    header('Content-type: application/json');
    $json = json_encode($jsonObcject);
    //echo $json;
    
}

