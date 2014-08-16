<?php

namespace App\AdminModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Tasks presenter.
 */
class TasksPresenter extends BasePresenter
{

    /** @var \App\Model\Facade\TaskFacade @inject */
    public $taskFacade;

    /** @var array */
    public $tasks;

    /** @var \App\Forms\TaskFormFactory @inject */
    public $taskFormFactory;

    /** @var \App\Model\Entity\Task */
    private $task;

    /** @var \App\Model\Facade\User @inject */
    public $userFacade;

    /** @var \App\Forms\CommentFormFactory @inject */
    public $commentFormFactory;

    /** @var \App\Model\Facade\CommentFacade @inject */
    public $commentFacade;

    /** @var \App\Model\Entity\Comment */
    private $comment;

    /** @var \App\Model\Facade\TimeFacade @inject */
    public $timeFacade;

    /** @var bool */
    private $clientMode;

    /** @var array */
    private $taskSolvers;
    
    /** @var \App\DateInterval */
    private $taskTotalTime;

    protected function startup()
    {
        parent::startup();
        $this->isAllowed("tasks", "view");
    }

    public function actionDefault()
    {
        $this->tasks = $this->taskFacade->findAll();
    }

    public function renderDefault()
    {
        $this->template->tasks = $this->tasks;
    }

    public function actionAdd()
    {
        $this->task = new \App\Model\Entity\Task;
        $this->taskFormFactory->setAdding();
        $this->setView("edit");
    }

    public function actionEdit($id)
    {
        $this->task = $this->taskFacade->find($id);
        $this->taskFormFactory->setEntityId($this->task->getId());
    }

    public function renderEdit()
    {
        $this->template->isAdd = $this->taskFormFactory->isAdding();
    }

    public function actionView($id)
    {
        $this->task = $this->taskFacade->find($id);
        if ($this->task === NULL) {
            $this->flashMessage("Requested task was not found.", 'error');
            $this->redirect("default");
        }

        $sender = $this->userFacade->find($this->getUser()->getId());
        $this->comment = new \App\Model\Entity\Comment;
        $this->comment->setSender($sender);
        $this->comment->setTask($this->task);
        $this->clientMode = $this->task->project->company->hasUser($sender);
        $this->taskSolvers = $this->taskFacade->findSolvers($this->task);
        $this->taskTotalTime = $this->timeFacade->getTotalTime($this->task);
    }

    public function renderView()
    {
        $this->template->task = $this->task;
        $this->template->clientMode = $this->clientMode;
        $this->template->solvers = $this->taskSolvers;
        $this->template->totalTime = $this->taskTotalTime;
        $this->template->_timeFacade = $this->timeFacade;
    }

    public function actionDelete($id)
    {
        $this->task = $this->taskFacade->find($id);
        if ($this->task) {
            $this->taskFacade->delete($this->task);
            $this->flashMessage("Entity was deleted.", 'success');
            $this->redirect("default");
        } else {
            $this->flashMessage("Entity was not found.", 'warning');
            $this->redirect("default");
        }
    }

    public function actionDeleteComment($id)
    {
        $this->flashMessage("Not implemented yet.", 'warning');
        $this->redirect("default");
    }

// <editor-fold defaultstate="collapsed" desc="privates">
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Forms">

    public function createComponentTaskForm()
    {
        $form = $this->formFactoryFactory
                ->create($this->taskFormFactory)
                ->setEntity($this->task)
                ->create();
        $form->onSuccess[] = $this->taskFormSuccess;
        return $form;
    }

    public function taskFormSuccess($form)
    {
        if ($form['submitContinue']->submittedBy) {
            $this->taskFacade->save($this->task);
            $this->redirect("edit", $this->task->getId());
        }
        $this->redirect("Tasks:");
    }

    public function createComponentCommentForm()
    {
        $form = $this->formFactoryFactory
                ->create($this->commentFormFactory)
                ->setEntity($this->comment)
                ->create();
        $form->onSuccess[] = $this->commentFormSuccess;
        return $form;
    }

    public function commentFormSuccess($form, $values)
    {
        if (!trim($values->message)) {
            $this->flashMessage("Message cannot be empty", "warning");
            $this->redirect("this");
        }
        $this->formFactoryFactory
                ->getEntityMapper()
                ->save($this->comment, $form);
        $this->comment->setSendTime(new Nette\Utils\DateTime);
        $this->comment->setPublic($form['sendPublic']->submittedBy);
        $this->commentFacade->save($this->comment);

        $this->redirect("this");
    }

// </editor-fold>
}
