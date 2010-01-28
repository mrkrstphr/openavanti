<?php

class UsersController extends Controller
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
        $search = new StandardSearch("users");
        
        $search->iResultsPerPage = 10;
        $search->sDefaultOrder = "last_name ASC";
        
        $search->process($page);

        $this->view->title = "Users";
        $this->view->search = $search;
        
        $form = new UserSearch();
        $form->loadSanitizedGet();
        
        $this->view->form = $form;
    
        $this->view->setViewScript("users/index.phtml");
    
    } // paginate()
    
    
    /**
     * The add page for adding new users. Simply sets the page title and
     * loads the view file.
     *
     * @returns void
     */
    public function add()
    {			
        $this->view->title = "Add User";
    
        $this->view->form = new UserEdit();
    
    } // add()
    
    
    /**
     * The view page for users. Pulls the requested user from the database and loads it into 
     * the form.
     *
     * @argument int The ID of the user to load into the form for viewing
     * @returns void
     */
    public function view($userId)
    {
        $user = new User();
        $user->find($userId);
        
        $this->view->user = $user;
        
        $this->view->title = $user->first_name . " " . 
            $user->last_name;
    
    } // edit()
    
    
    /**
     * The add page for adding new users. Pulls the requested user from the
     * database and loads it into the form.
     *
     * @argument int The ID of the user to load into the form for editing
     * @returns void
     */
    public function edit($userId)
    {
        $user = new User();
        $user->find($userId);
            
        if(empty($_POST))
        {
            $userRecord = $user->GetRecord();
            $userRecord->contact = $user->contact->GetRecord();
            
            Form::Load($userRecord);
        }
        
        $this->_view->user = $user;
        
        $this->_view->companies = Company::getActive();
        
        $this->_view->title = "Edit User";
    
    } // edit()
    
    
    /**
     * Validates a form submission and saves the modifications or adds a new
     * user record if validation is successful. Redirects to the edit page on
     * success, or redisplays the form with validation errors.
     *
     * @returns void
     */
    public function save()
    {
        $form = new UserEdit();
        
        $user = new User($form->loadSanitizedPost());
        
        $user->begin();
        
        if(!$user->saveAll())
        {
            $user->rollback();
            
            if(empty($form->_data["user_id"]))
            {
                $this->forwardAction('add');
            }
            else
            {
                $this->forwardAction('edit', null, array($form->_data["user_id"]));
            }
            
            return;
        }
        
        // TODO: Clean this up:
        
        if(!empty($_FILES["avatar_name"]["tmp_name"]))
        {
            $tmpName = FileFunctions::createFileNameFromTime($_FILES["avatar_name"]["name"]);
            
            copy($_FILES["avatar_name"]["tmp_name"], realpath("images/avatars") . "/" . $tmpName);
            
            $user->avatar_name = $tmpName;
            
            if(!$user->save())
            {
                if(empty($form->_data["user_id"]))
                {
                    $this->forwardAction("add");
                }
                else
                {
                    $this->forwardAction("edit", null, array($form->_data["user_id"]));
                }
                
                return;
            }
        }
        
        $user->commit();
        
        // Redirect to the edit page:
        
        $this->setFlash("User Saved Successfully");
        
        $this->redirectTo("/users/view/{$user->user_id}");
    
    } // save()
    
} // UsersController()

?>
