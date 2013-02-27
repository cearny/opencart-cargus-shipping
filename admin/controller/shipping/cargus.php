<?php
/*
 * @copyright Copyright 2013 Adrian Cearnau <cearny+github@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * @link https://github.com/cearny/opencart-cargus-shipping
 */
class ControllerShippingCargus extends Controller {
	private $error = array(); 
	
	public function index() {
		$this->language->load('shipping/cargus');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('cargus', $this->request->post);
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		foreach (array(
			'entry_status', 
			'entry_cod_client',
			'entry_pin', 
			'entry_serviciu_id', 
			'entry_tip_colet_id', 
			'entry_localitate_origine_id',
			'entry_retur_nt_semnata', 
			'entry_retur_alte_documente', 
			'entry_min_gratuit', 
			'entry_max_acceptabil_val',
			'entry_max_acceptabil_wgt'
		) as $key)
			$this->data[$key] = $this->language->get($key);
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		foreach (array(
			'error_warning',
			'error_serviciu_id',
			'error_tip_colet_id',
			'error_localitate_origine_id'
		) as $key)
			$this->data[$key] = isset($this->error[$key]) ? $this->error[$key] : '';

  		$this->data['breadcrumbs'] = array(
  			array(
	       		'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
	      		'separator' => false
	   		),
   			array(
	       		'text'      => $this->language->get('text_shipping'),
				'href'      => $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
	      		'separator' => ' :: '
	   		),
	   		array(
	       		'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('shipping/cargus', 'token=' . $this->session->data['token'], 'SSL'),
	      		'separator' => ' :: '
	   		)
   		);
		
		$this->data['action'] = $this->url->link('shipping/cargus', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		foreach (array(
			'cargus_status',
			'cargus_cod_client',
			'cargus_pin',
			'cargus_serviciu_id',
			'cargus_tip_colet_id',
			'cargus_localitate_origine_id',
			'cargus_retur_nt_semnata',
			'cargus_retur_alte_documente',
			'cargus_min_gratuit',
			'cargus_max_acceptabil_val',
			'cargus_max_acceptabil_wgt') as $key)
			$this->data[$key] = isset($this->request->post[$key]) ? $this->request->post[$key] : $this->config->get($key);
		
		$this->template = 'shipping/cargus.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
 		$this->response->setOutput($this->render());
	}
	
	private function validate() {
		// Permission check
		if (!$this->user->hasPermission('modify', 'shipping/cargus'))
			$this->error['warning'] = $this->language->get('error_permission');

		// Fields that must contain a value
		foreach (array(
			'cargus_serviciu_id' => array('error_serviciu_id', 'serviciu_id'),
			'cargus_tip_colet_id' => array('error_tip_colet_id', 'tip_colet_id'),
			'cargus_localitate_origine_id' => array('error_localitate_origine_id', 'localitate_origine_id')
		) as $key => $val)
			if (!$this->request->post[$key])
				$this->error[$val[1]] = $this->language->get($val[0]);

		// Serviciu ID must be one of { 2, 5 }
		if ((int)$this->request->post['cargus_serviciu_id'] != 2 && 
			(int)$this->request->post['cargus_serviciu_id'] != 5)
			$this->error['serviciu_id'] = $this->language->get('error_serviciu_id');

		// Tip colet ID must be one of { 1, 2 }
		if ((int)$this->request->post['cargus_tip_colet_id'] != 1 && 
			(int)$this->request->post['cargus_tip_colet_id'] != 2)
			$this->error['tip_colet_id'] = $this->language->get('error_tip_colet_id');

		// Localitate origine ID must be greater than zero
		if ((int)$this->request->post['cargus_localitate_origine_id'] <= 0)
			$this->error['localitate_origine_id'] = $this->language->get('error_localitate_origine_id');
		
		return (!$this->error);
	}
}
?>