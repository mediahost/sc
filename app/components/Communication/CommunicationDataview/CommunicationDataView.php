<?php

namespace App\Components;

use App\Model\Facade\CommunicationFacade;
use Nette\Utils\Html;


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
                $this->getContributors($communication),
                Html::el('a')->addAttributes([
                    'class' => 'grid-action-view btn btn-xs btn-mini',
                    'href' => $this->presenter->link(':App:Messages:', $communication->id)
                ])->add('<i class="s16 icomoon-icon-envelop"></i>')->render()
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
    
    public function getContributors(\App\Model\Entity\Communication $communication) {
        $result = [];
        foreach ($communication->contributors as $contributor) {
            $result[] = $contributor->user;
        }
        return implode(', ', $result);
    }
}

interface ICommunicationDataViewFactory
{

	/**
	 * @return CommunicationDataView
	 */
	public function create();

}