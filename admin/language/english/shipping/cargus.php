<?php
// Heading
$_['heading_title']                = 'Cargus';

$_['text_shipping']                = 'Livrare';
$_['text_success']                 = 'Success: Configuratia modulului a fost modificata!';

// Text
$_['entry_status']                 = 'Modulul este activ';
$_['entry_cod_client']             = 'Cod client <span class="help">Cod unic alocat fiecarui client. Este furnizat de catre Cargus. Se foloseste pentru a calcula tariful conform contractului pentru clientul respectiv. In cazul in care se doreste calcularea tarifului standard, acest camp se lasa necompletat.</span>';
$_['entry_pin']                    = 'Cod PIN <span class="help">Cod pin ce este folosit pentru a evita utilizarea frauduloasa a codului de client. Este furnizat de catre Cargus.</span>';

$_['entry_serviciu_id']            = 'Cod tip livrare<span class="help">Valori acceptate sunt 2 pentru serviciul Express si 5 pentru serviciul Matinal</span>';
$_['entry_tip_colet_id']           = 'Cod tip expeditie<span class="help">Valori acceptate sunt 1 pentru colet si 2 pentru plic</span>';
$_['entry_localitate_origine_id']  = 'Cod localitate expeditor<span class="help">Este un numar din lista de localitati furnizata de Cargus</span>';

$_['entry_retur_nt_semnata']       = 'Retur nota de transport semnata';
$_['entry_retur_alte_documente']   = 'Retur alte documente';

$_['entry_min_gratuit']            = 'Valoarea minima pentru care transportul devine gratuit';
$_['entry_max_acceptabil_val']     = 'Valoare maxima a comenzii acceptata pentru transport cu Cargus';
$_['entry_max_acceptabil_wgt']     = 'Greutatea maxima a comenzi acceptata pentru Transport cu Cargus';

$_['error_serviciu_id']            = 'Tipul de livrare trebuie specificat';
$_['error_tip_colet_id']           = 'Tipul de expeditie trebuie specificat';
$_['error_localitate_origine_id']  = 'Codul de localitate al expeditorului trebuie specificat';

// Error
$_['error_permission']             = 'Atentie: Nu ai dreptul sa efectuezi aceste modificari!';
?>