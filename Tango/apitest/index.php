<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* Configuraciones */

/* Token Tango */
define('TOKEN', "");

/* URL donde está instalado WooCommerce */
define('BASEURL', "https://www.argenlens.com.ar");

/* Nombre del archivo donde se reportan los problemas con Tango */
define('REPORTFILE', "report.txt");

/* Nombre del archivo donde se reportar los problemas con WooCommerce */
define('NOUPFILE', "noUpdate.txt");

/* ID lista de precios para Tango */
define ('PRICELISTID', 3);
/* ID Almacen para stock */
define ('WAREHOUSE', 4);

file_put_contents(REPORTFILE, date("y-m-d H:i")."\n");
file_put_contents(NOUPFILE, date("y-m-d H:i")."\n");
file_put_contents("soloprecio.txt", date("y-m-d H:i")."\n");

$inicio=microtime(true);

function testTangoConnection()
{
    $url="https://tiendas.axoft.com/api/Aperture/dummy";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('accesstoken:'.TOKEN, 'Content-Type: application/json', 'Content-Length: 0'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    return $response;
}
/* puede devolver más de un SKU porque busca las cadenas que contengan el SKU pasado. Filtrar y devolver SOLO el producto de SKU pedido */
function getPricePage($sSku)
{
    $url="https://tiendas.axoft.com/api/Aperture/Price?pageSize=500&pageNumber=1&filter=".PRICELISTID."&SKUCode=".urlencode($sSku);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('accesstoken:'.TOKEN, 'Content-Type: application/json', 'Content-Length: 0'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    $oResponse = json_decode($response);

    if(!isset($oResponse->Data)){
        $mensaje = "El SKU $sSku devolvió el siguiente resultado desde Tango para Precio.\n".var_export($oResponse, true)."\n\n";
        $mensaje.="-----------------------------------------------------------------------------------------------\n";
        file_put_contents(REPORTFILE, $mensaje, FILE_APPEND);
        return false;
    }

    if(sizeof($oResponse->Data) > 1){
        foreach ($oResponse->Data as $producto) {
            if($producto->SKUCode === $sSku){
                return $producto->Price;
            }
        }
    } else {
        return $oResponse->Data[0]->Price;
    }
}

function getStockPage($sSku)
{
    $url="https://tiendas.axoft.com/api/Aperture/Stock?pageSize=500&pageNumber=1&WarehouseCode=".WAREHOUSE."&filter=".urlencode($sSku);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('accesstoken:'.TOKEN, 'Content-Type: application/json', 'Content-Length: 0'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    $oResponse = json_decode($response);

    if(!isset($oResponse->Data)){
        $mensaje = "El SKU $sSku devolvió el siguiente resultado desde Tango para Stock.\n".var_export($oResponse, true)."\n\n";
        $mensaje.="-----------------------------------------------------------------------------------------------\n";
        file_put_contents(REPORTFILE, $mensaje, FILE_APPEND);
        return false;
    }

    if(sizeof($oResponse->Data) > 1){
        foreach ($oResponse->Data as $producto) {
            if($producto->SKUCode === $sSku){
                if(!empty($producto->Quantity)){
                    return $producto->Quantity;
                } else {
                    $mensaje = "El SKU $sSku devolvió el siguiente resultado desde Tango para Stock.\n".var_export($producto, true)."\n\n";
                    file_put_contents(REPORTFILE, $mensaje, FILE_APPEND);
                    return false;               
                }
            }
        }
    } else {
        return $oResponse->Data[0]->Quantity;
    }
}


function getWooProduct($sSku){
    file_put_contents("busca", $sSku."\n", FILE_APPEND);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, "");
    curl_setopt($ch, CURLOPT_URL, BASEURL."/wc-api/v3/products?filter[sku]=".urlencode($sSku));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
}

function getWooFullProduct(){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, "");
    curl_setopt($ch, CURLOPT_URL, BASEURL."/wc-api/v3/products?filter[limit]=-1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
}

function updateWooData($id, $aData, $parentid=null){
    if($parentid == null){
        $url = BASEURL."/wp-json/wc/v3/products/$id";
    } else {
        $url = BASEURL."/wp-json/wc/v3/products/$parentid/variations/$id";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, "");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $oResponse = json_decode($response);

    if($oResponse->id != $id){
        die("Ocurrió un error");
    } else {
        return true;
    }
    
}

function documentNotUpdate($sku, $precio, $stock, $onlyPrice){
    $mensaje = "";

    if(!$precio && !$stock){
        $mensaje = "No se pudieron obtener precio y Stock para $sku";
    } elseif(!$precio){
        $mensaje = "No se pudieron obtener precio para $sku";
    } elseif(!$stock){
        $mensaje = "No se pudieron obtener stock para $sku";
    } elseif($stock < 0){
        $mensaje = "$sku Tiene stock negativo($stock)";
    } else {
        $mensaje = "No se puedo determinar la razón por la cual no se actualizó los datos eran Precio = $precio, Stock = $stock";
    }

    if($onlyPrice){
        $mensaje.=" ------ Era lente de contactos -------";
    }

    file_put_contents(NOUPFILE, $mensaje."\n", FILE_APPEND);
}

$aPrecios = getWooFullProduct();


if(isset($aPrecios->products) && sizeof($aPrecios->products) > 0){
    $i=1;
    $faltanDatos=1;
    $stockNeg = 1;
    foreach ($aPrecios->products as $oArt) {
        $aData=array();
        if(in_array("Lentes de Contacto", $oArt->categories)){
            $onlyPrice=true;
        }else{
            $onlyPrice=false;
        }
        if(empty($oArt->sku) && sizeof($oArt->variations) > 0){
            foreach ($oArt->variations as $oVar) {
                if(empty($oVar->sku)){
                    $i++;
                    $faltanDatos++;
                    continue;
                }
                $precio = getPricePage($oVar->sku);
                $stock = getStockPage($oVar->sku);
                if($precio && $stock && $stock > 0 && !$onlyPrice){
                    $aData["regular_price"]=(string)$precio;
                    $aData["manage_stock"]=true;
                    $aData["stock_quantity"]=intval($stock);
                    updateWooData($oVar->id, $aData, $oArt->id);
                $i++;
                }elseif ($onlyPrice && $precio) {
                    $aData["manage_stock"] = false;
                    $aData["regular_price"]=(string)$precio;
                    $aData["in_stock"] = true;
                    updateWooData($oVar->id, $aData, $oArt->id);
                    file_put_contents("soloprecio.txt", $oVar->sku."\n", FILE_APPEND);
                    $i++;
                } elseif(!$precio || !$stock || $stock < 0){
                    $aData["manage_stock"]=true;
                    $aData["stock_quantity"]=0;
                    updateWooData($oVar->id, $aData, $oArt->id);
                    documentNotUpdate($oVar->sku, $precio, $stock, $onlyPrice);
                    $faltanDatos++;
                }
            }
        }else{
            $precio = getPricePage($oArt->sku);
            $stock = getStockPage($oArt->sku);
            if($precio && $stock && $stock > 0){
                //echo "SKU: $oArt->sku => precio = $precio - Stock = $stock"."<br />";
                $aData["regular_price"]=(string)$precio;
                $aData["manage_stock"]=true;
                $aData["stock_quantity"]=intval($stock);
                updateWooData($oArt->id, $aData);
            $i++;
            } elseif($onlyPrice && $precio){
                $aData["manage_stock"]=false;
                $aData["regular_price"]=(string)$precio;
                updateWooData($oArt->id, $aData);
                file_put_contents("soloprecio.txt", $oArt->sku."\n", FILE_APPEND);
                $i++;
            } elseif(!$precio || !$stock || $stock < 0){
                $aData["manage_stock"]=true;
                $aData["stock_quantity"]=0;
                updateWooData($oArt->id, $aData);
                documentNotUpdate($oArt->sku, $precio, $stock, $onlyPrice);
                $i++;
                $faltanDatos++;
            }
        }
    }
}
/*$wooProduct = getWooProduct("6LJO-ACVdIES");
echo '<pre>';
var_export(sizeof($wooProduct->products));
echo '</pre>';*/

$finalReport="\nTerminó en: ".(microtime(true)-$inicio)."\n";
$finalReport.="Total Productos Recorridos: ". $i."\n";
$finalReport.="Faltaron datos de $faltanDatos Productos, revise el log\n";
file_put_contents(REPORTFILE, $finalReport, FILE_APPEND);

$faltaron=file_get_contents(NOUPFILE);
mail("testcontact@domain.com", "Reporte Faltan Datos",var_export($faltaron, true));