<?php
class ControllerInformationTrekinetic extends Controller {
    public function index() {
        $this->load->language('information/contact');

        $this->document->setTitle('Trekinetic GTE Series 4 | All-Terrain Power Wheelchair | MobilityCare Australia');
        $this->document->setDescription('Enquire about the Trekinetic GTE Series 4 — a compact all-terrain power wheelchair for grass, gravel, compacted sand and uneven paths. Request product information, pricing or a trial with MobilityCare Australia.');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        $this->response->setOutput($this->load->view('information/trekinetic', $data));
    }
}
