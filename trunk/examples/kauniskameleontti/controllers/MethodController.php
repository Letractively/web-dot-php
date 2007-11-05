<?php
class MethodController extends Controller {

    function index() {
        $methods = $this->db->getTable('Method')->findAll();
        View::render('views/methods.php', array('methods' => $methods));
    }

    public function add() {

        $method = new Method();

	    if (Request::isPost()) {

            $form = new Zend_Form();
            $form->setDataSource($_POST);
            $form->addField('name')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', array(1,50, 'message' => array(
                    Zend_Validate_StringLength::TOO_SHORT => 'Nimen tulee olla vähintään yhden merkin mittainen.',
                    Zend_Validate_StringLength::TOO_LONG => 'Nimi saa olla korkeintaan viisikymmentä merkkiä pitkä.'
                ))
            );
            $form->addField('abbr')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', array(1,50, 'message' => array(
                    Zend_Validate_StringLength::TOO_SHORT => 'Lyhenteen tulee olla vähintään yhden merkin mittainen.',
                    Zend_Validate_StringLength::TOO_LONG => 'Lyhenne saa olla korkeintaan viisikymmentä merkkiä pitkä.'
                ))
            );

            if ($form->isValid()) {

                $data = $form->getData();

                $method->name = $data['name'];
                $method->abbr = $data['abbr'];

                try {
                    $method->save();
                    Flash::set('message', 'Maksutapa lisättiin onnistuneesti.');
                    Response::redirect('methods');
                } catch (Doctrine_Validator_Exception $e) {

                    Flash::set('method', $method);

                    $errors = array();

                    foreach($method->getErrorStack() as $field => $code) {

                        switch ($field) {
                            case 'name':

                                if ($code[0] == 'unique')
                                {
                                    $errors[] = 'Tietokannassa on jo saman niminen maksutapa.';
                                }
                                else
                                {
                                    $errors[] = 'Nimi on virheellinen.';
                                }

                                break;

                            case 'abbr':

                                if ($code[0] == 'unique')
                                {
                                    $errors[] = 'Tietokannassa on jo saman lyhenteinen maksutapa.';
                                }
                                else
                                {
                                    $errors[] = 'Lyhenne on virheellinen.';
                                }

                                break;
                        }

                    }

                    Flash::set('errors', $errors);

                    Response::redirect('methods/add');
                }

            } else {

                $data = $form->getData();

                $method->name = $data['name'];
                $method->abbr = $data['abbr'];

                Flash::set('method', $method);
                Flash::set('errors', $form->getMessages());

                Response::redirect('methods/add');
            }

	     } else {

	        if (Flash::has('method')) {
	            return View::render('views/methods-add.php', array('method' => Flash::get('method')));
	        }
	        else {
	            return View::render('views/methods-add.php', array('method' => $method));
	        }
	    }
	}
}