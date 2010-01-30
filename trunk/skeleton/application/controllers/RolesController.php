<?php

class RolesController extends Controller
{		
    
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
        $search = new StandardSearch("roles");
        
        $search->iResultsPerPage = 10;
        $search->sDefaultOrder = "name ASC";
        
        $search->process($page);

        $this->view->title = "Roles";
        $this->view->search = $search;
        
        $form = new RoleSearch();
        $form->loadSanitizedGet();
        
        $this->view->form = $form;
    
        $this->view->setViewScript("roles/index.phtml");
    
    } // paginate()
    
    
    /**
     * The add page for adding new roles. Simply sets the page title and
     * loads the view file.
     *
     * @returns void
     */
    public function add()
    {			
        $this->view->title = "Add Role";
    
        $this->view->form = new RoleEdit();
        $this->view->form->loadSanitizedPost();

    } // add()
    
    
    /**
     * The view page for roles. Pulls the requested role from the database and loads it into 
     * the form.
     *
     * @argument int The ID of the role to load into the form for viewing
     * @returns void
     */
    public function view($roleId)
    {
        $role = new Role();
        $role->find($roleId);
        
        $this->view->role = $role;
        
        $this->view->title = 'Role: ' . $role->name;
    
    } // edit()
    
    
    /**
     * The add page for adding new roles. Pulls the requested role from the
     * database and loads it into the form.
     *
     * @argument int The ID of the role to load into the form for editing
     * @returns void
     */
    public function edit($roleId)
    {
        $form = new RoleEdit();

        $role = new Role();
        $role->find($roleId);
        
        $form->loadData($role->getRecord());
        $form->loadSanitizedPost();

        $this->view->role = $role->getRecord();
        
        $this->view->form = new $form;

        $this->view->title = "Edit Role";
    
    } // edit()
    
    
    /**
     * Validates a form submission and saves the modifications or adds a new
     * role record if validation is successful. Redirects to the edit page on
     * success, or redisplays the form with validation errors.
     *
     * @returns void
     */
    public function save()
    {
        $form = new RoleEdit();
        
        $role = new Role($form->loadSanitizedPost());
        
        $role->begin();
        
        if(!$role->saveAll())
        {
            $role->rollback();
            
            if(empty($form->_data["role_id"]))
            {
                $this->forwardAction('add');
            }
            else
            {
                $this->forwardAction('edit', null, array($form->_data["role_id"]));
            }
            
            return;
        }
        
        $role->commit();
        
        // Redirect to the edit page:
        
        $this->setFlash("Role Saved Successfully");
        
        $this->redirectTo("/roles/view/{$role->role_id}");
    
    } // save()
    
} // RolesController()

?>
