<?php
class ModelShippingCargus extends Model {
    function getQuote($address) {
        $this->load->language('shipping/cargus');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country where country_id = '" . (int)$address['country_id'] . "' and name='Romania'");
        $status = !!$query->num_rows;

        if (!$status) return array();

        // No need to do anything *at all* if there are no products in the cart
        if (!$this->cart->hasProducts()) return array();

        // Now, get the currency value multiplier, since our Cargus-provided currency is always in RON.
        $query_currency_multiplier = $this->db->query("SELECT COALESCE(value, 1.0) AS value FROM " . DB_PREFIX . "currency WHERE code = 'RON' LIMIT 0, 1");
        $currency_multiplier = (float)$query_currency_multiplier->row['value'];
        // Protect against divide-by-zero
        if ($currency_multiplier == 0)
            $currency_multiplier = 1.0;

        // Setari de comportament pentru modul
        //
        // $min_gratuit - valoarea minima pentru care transportul devine gratuit
        // TODO: Currently unused
        $min_gratuit = $this->config->get('cargus_min_gratuit');
        // $max_acceptabil_val - valoarea maxima pentru care transportul cu Cargus este acceptabil
        // TODO: Currently unused
        $max_acceptabil_val = $this->config->get('cargus_max_acceptabil_val');
        // $max_acceptabil_wgt - greutatea maxima pentru care transportul cu Cargus este acceptabil
        // TODO: Currently unused
        $max_acceptabil_wgt = $this->config->get('cargus_max_acceptabil_wgt');
        // $cod_payment_methods - metodele de plata care implica rambursare prin Cargus
        $cod_payment_methods = $this->config->get('cargus_cod_payment_methods');

        // Setari care vor fi trimise direct la Cargus:
        //
        // $_POST['cod_client'] - cod unic alocat fiecarui client. Este furnizat de catre Cargus. Se foloseste pentru 
        // a calcula tariful conform contractului pentru clientul respectiv. 
        // In cazul in care se doreste calcularea tarifului standard, acest camp se lasa necompletat.
        $client_code = $this->config->get('cargus_cod_client');
        // POST['pin'] - cod pin ce este folosit pentru a evita utilizarea frauduloasa a codului de client. Este 
        // furnizat de catre Cargus.
        $client_pin = $this->config->get('cargus_pin');
        // $_POST['serviciu_id'] - Tipul de livrare. Se va completa 2 pentru serviciul Express sau 5 pentru cel Matinal.
        $service_id = $this->config->get('cargus_serviciu_id');
        // $_POST['tip_colet_id'] - 1 - colet; 2 - plic
        $delivery_type = $this->config->get('cargus_tip_colet_id');
        // $_POST['retur_nt_semnata'] - reprezinta serviciul "Retur nota de transport semnata"; poate fi 1 sau 0
        $return_transport_note = $this->config->get('cargus_retur_nt_semnata');
        // $_POST['retur_alte_documente'] - reprezinta serviciul "Retur alte documente"; poate fi 1 sau 0
        $return_other_documents = $this->config->get('cargus_retur_alte_documente');
        // $_POST['localitate_origine_id'] - (int) id-ul localitatii de origine (furnizat de Cargus)
        $shipper_city_id = $this->config->get('cargus_localitate_origine_id');
        // $_POST['centru_origine_id'] - (int) id-ul centrului Cargus de care apartide localitatea de origine (furnizat de Cargus)
        //                             - OPTIONAL - daca este completat codul de localitate, nu mai conteaza acesta
        $shipper_cargus_center_id = $this->config->get('cargus_centru_origine_id');

        // Alte campuri care trebuiesc trimise:
        // $_POST['tip_platitor'] - 1 - expeditor; 2 - destinatar; evident, in cazul acesta plata se face la expeditor
        // $_POST['localitate_destinatar_id'] - (int) id-ul localitatii de destinatie (furnizat de Cargus)
        // $_POST['centru_destinatar_id'] - (int) id-ul centrului Cargus de care apartide localitatea de destinatie (furnizat de Cargus)
        //                                - OPTIONAL - daca este completat codul de localitate, nu mai conteaza acesta
        // $_POST['flag_asigurare'] - 1 - se doreste asigurare; 0 - fara asigurare; este 1 daca se doreste ramburs
        // $_POST['valoare_asigurata'] - suma pentru care se doreste asigurarea expedierii; numar real, pozitiv (separator zecimal: punct);
        //                               nu poate fi zero daca $_POST['flag_asigurare'] este 1
        // $_POST['suma_ramburs'] - in cazul in care se opteaza pentru serviciul ramburs, trebuie completata suma ce se doreste a fi
        //                          returnata; trebuie sa fie identica cu valoarea asigurata; numar real, pozitiv (separator zecimal: punct)
        // $_POST['valoare_declarata'] - valoarea expedierii; se completeaza in cazul in care $_POST['valoare_asigurata'] sau
        //                               $_POST['suma_ramburs'] sunt diferite de zero; numar real, pozitiv (separator zecimal: punct)
        // $_POST['greutate'] - greutatea expedierii; numar real, pozitiv, mai mare ca 0 (separator zecimal: punct)
        //                      NOTA: probabil ca e in KG, nu scrie in documentatie
        // $_POST['volum_nt'] - greutatea volumetrica a expedierii; se calculeaza dupa formula (L x l x h) / 6000; numar real, pozitiv
        //                    - OPTIONAL - daca este completata greutatea fizica, nu este necesar (important doar pentru produse voluminoase)

        $msg = "Comanda nu poate fi procesată de Cargus.<br>Va rugam sa corectati datele de livrare conform mesajului de mai jos: <br><br>";

        // Our output variable
        $method_data = array();
        // Our error message combiner (in case we hit an error)
        $error = '';

        // Clean up some settings
        if (is_numeric($min_gratuit)) $min_gratuit = $min_gratuit + 0; else $min_gratuit = 0 + 0;
        if (is_numeric($max_acceptabil_val)) $max_acceptabil_val = $max_acceptabil_val + 0; else $max_acceptabil_val = 0 + 0;
        if (is_numeric($max_acceptabil_wgt)) $max_acceptabil_wgt = $max_acceptabil_wgt + 0; else $max_acceptabil_wgt = 0 + 0;

        // Now here's an interesting question - do we calculate shipping for everything in the cart or just for shippable products?
        // Technically, it should depend on the payment method - if it's COD, we'll charge for everything, including extra services.
        // TODO: Make this actually work
        $cod_in_use = true; // in_array($this->session->data['shipping_method']['code'], explode(',', $cod_payment_methods));
        // We always insure our parcels (maybe make this optional?)
        $insurance_in_use = 1;

        $insured_value = 0;
        $cod_value = 0;
        // Original code: number_format(round((float)$this->cart->getWeight(),0), 0, '.', ''); (might need it)
        $weight = $this->cart->getWeight();
        // For now, we set the volumetric weight to be the same as the real weight
        $volumetric_weight = $weight;

        // If we're using COD, use the entire value of the cart, otherwise add up only the values of the shippable products
        if ($cod_in_use)
            // Original code: number_format(round((float)$this->cart->getTotal(),2), 2, '.', ''); (might need it)
            $insured_value = $cod_value = $this->cart->getTotal();
        else
            foreach ($this->cart->getProducts() as $product) {
                if (!!$product['shipping']) $insured_value += $product['total'];
            }

        // Not sure why this field ($declared_value) needs to be sent over; of course, the declared value is the same as the insured value
        // Multiply to the correct currency on display
        // TODO: Are we sure this is right, how can we check what currency is on display now in OpenCart? Is there a $session[] var?
        $declared_value = $insured_value = round($insured_value * $currency_multiplier, 2);
        $cod_value = round($cod_value * $currency_multiplier, 2);

        // The shipper pays for the shipping costs; the other option is 2 (the receiving party), but this makes no sense in
        // an online store scenario, so don't bother adding a module setting for this (for now)
        $payer_id = 1;

        // TODO: Make this work in a later version of PHP.
        $clean_city_name = $this->db->escape(strtoupper($address['city']));
        $query_id_localitate = $this->db->query("SELECT * FROM " . DB_PREFIX . "cargus_zone_city_mapping WHERE zone_id = " .(int)$address['zone_id']. " AND city = '" .$clean_city_name. "' LIMIT 0, 1");

        // Try and match the city name to our list of city codes
        $destination_city_id = -1;
        if ($query_id_localitate->num_rows) $destination_city_id = $query_id_localitate->row['city_id'];

        // If we couldn't match a city name, return with an error
        // TODO: Also try searching by postal code
        if ($destination_city_id == -1) return array();
        
        $post_data = array(
            'cod_client' => $client_code,
            'pin' => $client_pin,
            'tip_platitor' => $payer_id,
            'serviciu_id' => $service_id,
            'tip_colet_id' => $delivery_type,
            'retur_nt_semnata' => $return_transport_note,
            'retur_alte_documente' => $return_other_documents,
            'localitate_origine_id' => $shipper_city_id,
            'localitate_destinatar_id' => $destination_city_id,
            'flag_asigurare' => $insurance_in_use,
            'valoare_asigurata' => $insured_value,
            'suma_ramburs' => $cod_value,
            'valoare_declarata' => $declared_value,
            'greutate' => $weight,
            'volum_nt' => $volumetric_weight
        );

        // Perform the actual HTTP POST request (this should really be HTTPS but Cargus doesn't provide that)
        // TODO: Move this code to a separate method
        $url = 'http://webexpress.cargus.ro/calcul_tarif.php';
        $c = curl_init ($url);
        // For some reason, Cargus doesn't like curl's User-agent header, so set something more acceptable
        curl_setopt($c,CURLOPT_USERAGENT,'Mozilla/5.0');
        curl_setopt ($c, CURLOPT_POST, true);
        // We don't use the shipper Cargus Center ID for now, just the City ID; nor do we use the receiver Cargus Center ID
        //
        // TODO: For some reason, we're getting a MUCH better rate if Cargus is handling the COD duties
        // than when they only perform the shipping; they need to be asked if it's an error in their system.
        curl_setopt ($c, CURLOPT_POSTFIELDS, http_build_query($post_data));
            
        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
        $xml_response = curl_exec ($c);
        curl_close ($c);

        // TODO: There's a slight problem here with the way the Cargus web service handles erroneous input values.
        // Honestly, it NEVER actually complains about ANYTHING, so we don't have any checks available to make sure
        // that the returned data is good, except that we should have a total shipping value greater than zero.

        // Look for the VAT-free and VAT-included totals
        if (!strpos($xml_response, "<total_fara_tva>") || !strpos($xml_response, "<total_tarif>")) {
            // We couldn't find the totals, so assume it's an error of some kind
            // TODO: Format a nice error message for the user
            return array();
        }

        // Get the VAT-free total
        $matches = array();
        if (!preg_match("/[0-9]+\\.?[0-9]*/", substr($xml_response, strpos($xml_response, "<total_fara_tva>")), $matches)) {
            // TODO: Format a nice error message for the user
            return array();
        }
        $total_excluding_vat = (float)$matches[0];

        // Same for the VAT-included total
        $matches = array();
        if (!preg_match("/[0-9]+\\.?[0-9]*/", substr($xml_response, strpos($xml_response, "<total_tarif>")), $matches)) {
            // TODO: Format a nice error message for the user
            return array();
        }
        $total_including_vat = (float)$matches[0];

        // A zero shipping cost means an error in the provided data
        if ($total_excluding_vat == 0.0 || $total_including_vat == 0.0) return array();

        // Now, multiply to the correct currency on display
        // TODO: Are we sure this is right, how can we check what currency is on display now in OpenCart? Is there a $session[] var?
        $total_excluding_vat = round($total_excluding_vat / $currency_multiplier, 2);
        $total_including_vat = round($total_including_vat / $currency_multiplier, 2);

        $quote_data['cargus'] = array(
            'code'         => 'cargus.express',
            'title'        => "Cargus Express",
            'cost'         => $total_including_vat,
            // TODO: Currently not needed
            // 'tax_class_id' => 'cargus.tax',
            'text'         => $this->currency->format($total_including_vat)
        );

        $method_data = array(
            'code'       => 'cargus',
            'title'      => 'Cargus',
            'quote'      => $quote_data,
            'sort_order' => '1',
            'error'      => $error
        );

        return $method_data;
    }
}
?>
