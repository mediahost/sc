<?php

namespace App\Components;

use App\Model\Facade\CommunicationFacade;


class CommunicationDataView extends BaseControl {
    
    /** @var Entity\Communication[] */
	protected $communications = [];
    
    /** @var CommunicationFacade @inject */
	public $communicationFacade;
    
    
    /**
     * @inheritdoc
     */
    public function render() {
        $this->template->communications = $this->communicationFacade->getAllCommunications();
        parent::render();
    }
    
    
    public function handleFilter() {
        $request = $this->presenter->getRequest();
        $search = $request->post['search']['value'];
        $draw = $request->post['draw'];
        $communications = $this->communicationFacade->getAllCommunications($search);
        $data = [];
        foreach ($communications as $communication) {
            $data[] = [
                $communication->id,
                $communication->subject,
                $communication->lastMessage->time->format('d.m.Y') . ' - ' .
                substr($communication->lastMessage->text, 0, 50),
                $communication->getOppositeName($this->template->user->identity),
                ''
            ];
        }
        $data = [
            "draw" => $draw,
            "recordsTotal" => count($communications),
            "recordsFiltered" => count($communications),
            "data" => $data
        ];
        $this->presenter->sendJson($data);
    }
}

interface ICommunicationDataViewFactory
{

	/**
	 * @return CommunicationDataView
	 */
	public function create();

}