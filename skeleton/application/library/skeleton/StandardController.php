<?php

abstract class StandardController extends Controller
{
    protected $_elementName = "";
    protected $_singularElementName = "";

    
    public function init()
    {
        $this->_singularElementName = !empty($this->_singularElementName) ?
            $this->_singularElementName : StringFunctions::toSingular($this->_elementName);
    }

    /**
     * The default page for the user module. Calls the pagination function
     * and defaults to the first page if none provided
     *
     * @arguments int The page number of search results to display
     * @returns void
     */
    public function index()
    {
        $this->paginate();
    
    } // index()
    
    
    /**
     * Displays users in the system based on search results, pagination,
     * and sorting options.
     *
     * @argument int The page number of search results to display
     * @returns void
     */
    public function paginate($page = 1)
    {
        $search = new StandardSearch($this->_elementName);
        
        $search->iResultsPerPage = 10;
        
        $search->sDefaultOrder = "{$this->_defaultOrder}";
        
        $search->process($page);
        
        $this->view->title = ucwords(str_replace("_", " ", $this->_elementName));
        $this->view->search = $search;
        
        $normalizedName = str_replace(" ", "", ucwords(str_replace("_", " ",
            $this->_singularElementName)));
        
        $formClass = "{$normalizedName}Search";
        $form = new $formClass();
        $form->loadSanitizedGet();
        
        $this->view->form = $form;
        
        $this->view->setViewScript("{$this->_elementName}/index.phtml");
        
    } // paginate()
    
    
    /**
     * The add page for adding new records. Simply sets the page title and
     * loads the view file.
     *
     * @returns void
     */
    public function add()
    {			
        $this->view->title = "Add " . ucwords(str_replace("_", " ", $this->_singularElementName));;
    
        $normalizedName = str_replace(" ", "", ucwords(str_replace("_", " ",
            $this->_singularElementName)));
        
        $formClass = "{$normalizedName}Edit";
        
        $this->view->form = new $formClass();
        $this->view->form->loadSanitizedPost();

    } // add()
    
    
    /**
     * The view page for records. Pulls the requested record from the database and loads it into 
     * the form.
     *
     * @argument int The ID of the record to load into the form for viewing
     * @returns void
     */
    public function view($recordId)
    {
        $normalizedName = str_replace(" ", "", ucwords(str_replace("_", " ",
            $this->_singularElementName)));
        
        $record = new $normalizedName();
        $record->find($recordId);
        
        $this->view->record = $record;
        
        $this->view->title = 'View ' . ucwords(str_replace("_", " ", $this->_elementName));
    
    } // view()
    
    
    /**
     * The add page for adding new records. Pulls the requested record from the
     * database and loads it into the form.
     *
     * @argument int The ID of the record to load into the form for editing
     * @returns void
     */
    public function edit($recordId)
    {
        $normalizedName = str_replace(" ", "", ucwords(str_replace("_", " ",
            $this->_singularElementName)));
        
        $formName = "{$normalizedName}Edit";
        
        $form = new $formName();
        
        $record = new $normalizedName();
        $record->find($recordId);
        
        if($this->getRequest()->isPostEmpty())
            $form->loadData($record->getRecord());
        else
            $form->loadSanitizedPost();
        
        $this->view->record = $record->getRecord();
        
        $this->view->form = $form;
        
        $this->view->title = "Edit " . ucwords(str_replace("_", " ", $this->_elementName));
    
    } // edit()
    
    
    /**
     * Validates a form submission and saves the modifications or adds a new
     * record if validation is successful. Redirects to the edit page on
     * success, or redisplays the form with validation errors.
     *
     * @returns void
     */
    public function save()
    {
        $normalizedName = str_replace(" ", "", ucwords(str_replace("_", " ",
            $this->_singularElementName)));
        
        $idName = $this->_singularElementName . "_id";
        
        $formName = "{$normalizedName}Edit";
        
        $form = new $formName();
        
        $record = new $normalizedName($form->loadSanitizedPost());
        
        $record->begin();
        
        if(!$record->saveAll())
        {
            $record->rollback();
            
            if(empty($form->_data[$idName]))
            {
                $this->forwardAction('add');
            }
            else
            {
                $this->forwardAction('edit', null, array($form->_data[$idName]));
            }
            
            return;
        }
        
        $record->commit();
        
        // Redirect to the edit page:
        
        $this->setFlash(ucwords(str_replace("_", " ", $this->_singularElementName)) . " Saved Successfully");
        
        $id = $record->$idName;
        
        $this->redirectTo("/" . $this->_elementName . "/view/{$id}");
    
    } // save()
    
} // StandardController()

?>
