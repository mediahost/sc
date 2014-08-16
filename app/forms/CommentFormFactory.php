<?php

namespace App\Forms;

/**
 * CommentFormFactory
 *
 * @author Petr Poupě
 */
class CommentFormFactory extends FormFactory
{

    public function create()
    {
        $form = $this->formFactory->create();
        $form->addTextArea('message', 'Message', NULL, 4)
                ->setAttribute("placeholder", "Type a message here...");
        $form->addTouchSpin("minutes", "Minutes")
                ->setPrefix("<i class=\"fa fa-clock-o\"></i>")
                ->setPostfix("min.")
                ->setMin(0)
                ->setMax(480)
                ->setButtonDownClass("btn red")
                ->setButtonUpClass("btn green") 
                ->setDefaultValue(5);
        
        $form->addSubmit('sendPrivate', 'Private');
        $form->addSubmit('sendPublic', 'Public');
        return $form;
    }

}
