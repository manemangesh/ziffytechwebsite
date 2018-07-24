<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business extends MY_Controller {
    public function __construct()
    {
                parent::__construct();
                // Your own constructor code
                $this->load->database();
                $this->load->helper('login_helper');
                $this->load->helper('security');
    }
      public function dashboard(){
      	 //print_r('hi');die;
        $usertype = _get_current_user_type_id($this);
       
        if(_is_user_login($this)){
        	
            $from_date = date("Y-m-d");
            $to_date = date("Y-m-d");
            if($this->input->post("date_range")){
                $date_range =  $this->input->post("date_range");
                $date_range = explode(',',$date_range);
                $from_date = trim($date_range[0]);
                $to_date = trim($date_range[1]);    
            }
            $user_id = _get_current_user_id($this);
            $q =$this->db->query('select bus_id from business where user_id ='.$user_id);
            $bus_id=$q->row()->bus_id;
            $data['date_range_lable'] = $this->input->post('date_range_lable');
            $data["app_count"]= $this->business_model->get_business_today_count($bus_id);
            $data["today"]=$this->business_model->get_business_today_revenue($bus_id);
            $data["week"]=$this->business_model->get_business_week_revenue($bus_id);
            $data["month"]=$this->business_model->get_business_month_revenue($bus_id);
              // print_r($data["revenue"]);die; 
                $data["appointments"] = $this->business_model->get_business_appointment("",$user_id,date("Y-m-d"),date("Y-m-d"));
                /*$data["chart_appointment"] = $this->business_model->get_business_appointment_group($from_date,$to_date,$user_id);*/
                $data["reviews_count"] = $this->business_model->get_reviews_counts("",$user_id);
                $data["user_count"] =  $this->users_model->get_walkin_counts($bus_id);
                $data['bus_id']=$bus_id;
            
                $this->load->view("business/dashboard",$data);
            
        }
        else
	    {
			redirect('admin');
	    }   
    }
      public function list_business()
	  {
	   if(_is_user_login($this)){
	       $data["error"] = "";
	       $data["active"] = "business";
           
           if(_get_current_user_type_id($this)==3){

                $data["business"] = $this->business_model->get_businesses($userid=3);
                //print_r($data);die;
                $this->load->view('admin/business/list2',$data);
           }
           
        }
        else
        {
            redirect('admin');
        }
    }
/* business service area */
	   public function business_edit(){
        if(_is_user_login($this)){
        	$id=_get_current_user_id($this);
            $data = array("error"=>"");
			$update_password=0;
            $users=array();  
            $q=$this->db->query('select user_id from business where bus_id ='.$id);
                $user_id=$q->row()->user_id;
                $this->load->library('form_validation');
                $this->form_validation->set_rules('bus_title', 'Clinic Title', 'trim|required');
				$this->form_validation->set_rules('bus_logo', 'Clinic Image', 'trim|callback_validate_image_bus');
                $this->form_validation->set_rules('start_con_time', 'Clinic Start Time', 'trim|required');
                $this->form_validation->set_rules('end_con_time', 'Clinic End Time', 'trim|required');
                $this->form_validation->set_rules('bus_email', 'Contact Address', 'trim|required|valid_email');
                $this->form_validation->set_rules('user_email', 'User Email Id', 'trim|required|valid_email');
                $this->form_validation->set_rules('bus_contact', 'Contact Number', 'trim|required');
                $this->form_validation->set_rules('user_password', 'password', 'trim|required');
                $this->form_validation->set_rules('conf_password', 'Confirm Password', 'trim|required|matches[user_password]');
                //$this->form_validation->set_rules('buscat[]', 'Category Name', 'trim|required');
                //$this->form_validation->set_rules('lat_log', 'Latitude Longitude', 'trim|required');
                //$this->form_validation->set_rules('location', 'Business Location ', 'trim|required');
              if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
					
                    $busslug = url_title($this->input->post('bus_title'), 'dash', TRUE);
                    $lat = $this->input->post("lat");
           		 	$lon = $this->input->post("lon");
            		$address=$this->input->post("address");
                   
                $savebus = array(
                            "bus_title"=>$this->input->post("bus_title"),
                            "bus_slug"=>$busslug,
                            "bus_email"=>$this->input->post("bus_email"),
                            "bus_description"=>$this->input->post("busdesc"),
                            "bus_contact"=>$this->input->post("bus_contact"),
                            "bus_google_street"=>$this->input->post("address"),
                            "start_con_time"=>$this->input->post("start_con_time"),
                            "end_con_time"=>$this->input->post("end_con_time"),
                            "bus_latitude"=>$lat,
                            "bus_longitude"=>$lon,
                            "bus_fee"=>$this->input->post('bus_fee'),
                            "bus_con_time"=>$this->input->post('bus_con_time'),
                            "city_id"=>$this->input->post('city_id'),
                            "country_id"=>$this->input->post('country_id'),
                            "locality_id"=>$this->input->post('locality_id')
                );
                     
                if($_FILES["bus_logo"]["size"] > 0){
                    $config['upload_path']          = './uploads/profile/';
                    $config['allowed_types']        = 'gif|jpg|png|jpeg';
                    $this->load->library('upload', $config);
    
                    if ( ! $this->upload->do_upload('bus_logo'))
                    {
                    	$this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->upload->display_errors().'
                                    </div>');
                            //$error = array('error' => $this->upload->display_errors());
                    }
                    else
                    {
                        $img_data = $this->upload->data();
                        $savebus["bus_logo"]=$img_data['file_name'];
                        //$users['user_image']=$img_data['file_name'];
                        //print_r($savebus);
                        //print_r($users);die;
                    } 
               }
                  $this->db->update("business",$savebus,array("bus_id"=>$id)); 
                  $user_password=$this->input->post('user_password');
                  $user=$this->db->select('*')->from('business')->where('user_email',$this->input->post('user_email'))->get()->row();
                  //print_r($user->user_password);die;
                  if($user->user_password != $user_password && trim($user_password) != ""){
                            $users["user_password"]= md5($user_password);
							$update_password= 1;
                        }
                    
                  /*   if($_FILES["bus_logo"]["size"] > 0){
                    $config['upload_path']          = './uploads/profile/';
                    $config['allowed_types']        = 'gif|jpg|png|jpeg';
                    $this->load->library('upload', $config);
    
                    if ( ! $this->upload->do_upload('bus_logo'))
                    {
                            $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->upload->display_errors().'
                                    </div>');
                    }
                    else
                    {
                        $img_data = $this->upload->data();
                        //$users['user_image']=$img_data['file_name'];
                         print_r($users);die;
                    }
               } */
                  $this->db->where('bus_id',$id);
                  $this->db->update('business',array('kyc_status'=>0));
                  $users['user_email']=$this->input->post('user_email');
                  //print_r($users);die;
                   if(!empty($users))
                   {
				   	$this->db->where('user_id',$user_id);
                    $this->db->update("business",$users); 
				   }
                    
                    $this->db->query("Delete from business_category where bus_id = '".$id."'");

					$bus_title=$this->db->select('bus_title')->from('business')->where('bus_id',$id)->get()->row()->bus_title; 
              $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>'41',
                        'notification'=>$bus_title." has Updated his profile at ".date('Y-m-d H:i:s'),
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s')); 
                       $n_id=$this->db->insert('doctor_notification',$notification);                
                    foreach($_REQUEST["buscat"] as $cat){
                            $this->common_model->data_insert("business_category",array("bus_id"=>$id,"category_id"=>$cat));
                    }
                       if($update_password  == '1')
						{
							$this->session->sess_destroy();
							redirect('admin');
						}
                    $this->session->set_flashdata("message",'<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Your Clinic Details Updated information has been send to admin for verification. 
                                    </div>');
                    redirect('business/list_business/'.$id);
                    //redirect('admin/business/');
               	}
            
	        $data["error"] = "";
	        $data["categor"] = $this->category_model->sel_categories();
            $data["buscat"] = $this->category_model->bus_category($id);
	        $data["setbuss"] = $this->business_model->set_listing($id);
            $data["countries"] = $this->area_model->get_countries("1");
            $data["localities"] = $this->area_model->get_locality("1",$data["setbuss"]->country_id,$data["setbuss"]->city_id);
            $data["cities"] = $this->area_model->get_cities("1",$data["setbuss"]->country_id);
           // $data["users"] = $this->users_model->get_users(array("user_type"=>3));
          // print_r($data);die;
           $this->load->view('business/edit_business',$data); 
        }
        else
        {
            redirect('admin');
        }
        
    }
    
	  public function validate_image_bus() {
		  //print_r(1);die;
       $check = TRUE;
       if (isset($_FILES['bus_logo']) && $_FILES['bus_logo']['size'] != 0)   {
       $allowed =  array('gif','png','jpg','jpeg');
       $ext = pathinfo($_FILES['bus_logo']['name'],PATHINFO_EXTENSION);
        if(!in_array($ext,$allowed)){
			$this->form_validation->set_message('validate_image_bus', "Invalid file extension");
            $check = FALSE;
            return $check;
        }
    }
    return $check;
   }
	
       public function business_service($id){
        if(_is_user_login($this)){
            $data = array();
             $bus_id = _get_current_user_id($this);
            if($_POST){
                $this->load->library('form_validation');
                
                $this->form_validation->set_rules('bus_title', 'Service Title', 'trim|required');
                //$this->form_validation->set_rules('bus_price', 'Service Price', 'trim|required|numeric');
                //$this->form_validation->set_rules('service_tax', 'Service Tax', 'trim|numeric');
                //$this->form_validation->set_rules('total_cost', 'Total Cost', 'trim|numeric');
                //$this->form_validation->set_rules('bus_time', 'Service Time', 'trim|required');
                if ($this->form_validation->run() == FALSE) 
        		{
        		   if($this->form_validation->error_string()!=""){
        			$data["error"] = '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                </div>';
					}
                    
        		}else
                {
                        $bus_title = $this->input->post("bus_title");
                        $bus_time = $this->input->post("bus_time");
                        $bus_price = $this->input->post("bus_price");
                        $service_tax = $this->input->post("service_tax");
                        $total_cost=$this->input->post("total_cost");
                            $this->common_model->data_insert("business_services",
                                array(
                                "service_title"=>$bus_title,
                                /*"business_approxtime"=>$bus_time,
                                "service_price"=>$bus_price,
                                "service_tax"=>$service_tax,
                                "total_cost"=>$total_cost,*/
                                "bus_id"=>$id
                               ));
                            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Clinic Service Added Successfully
                                </div>');
                                //$curr_url = current_url();
                                redirect('business/business_service/'.$bus_id);
                }
            }

           $data["business_service"] = $this->business_model->get_business_service($id);
           $data['bus_id']=$id;
           $this->load->view("business/business_service",$data);
        }
         else
        {
            redirect('admin');
        }
    }
	   public function doctor_service($bus_id,$id){
        if(_is_user_login($this)){
            $data = array();
            
            if($_POST){
                $this->load->library('form_validation');
                
                $this->form_validation->set_rules('bus_title', 'Busoness Service Title', 'trim|required');
                $this->form_validation->set_rules('bus_price', 'Business Service Price', 'trim|required|numeric');
                $this->form_validation->set_rules('service_tax', 'Business Service Tax', 'trim|numeric');
                $this->form_validation->set_rules('total_cost', 'Total Cost', 'trim|numeric');
                $this->form_validation->set_rules('bus_time', 'Business Service Time', 'trim|required');
                if ($this->form_validation->run() == FALSE) 
        		{
        		   if($this->form_validation->error_string()!=""){
        			$data["error"] = '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                </div>';
					}
                    
        		}else
                {
                         
                        $bus_title = $this->input->post("bus_title");
                        $bus_time = $this->input->post("bus_time");
                        $bus_price = $this->input->post("bus_price");
                        $service_tax = $this->input->post("service_tax");
                        $total_cost=$this->input->post("total_cost");
                        

                            $this->common_model->data_insert("business_services",
                                array(
                                "service_title"=>$bus_title,
                                "business_approxtime"=>$bus_time,
                                "service_price"=>$bus_price,
                                "service_tax"=>$service_tax,
                                "total_cost"=>$total_cost,
                                "doct_id"=>$id
                               ));
                            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Business Service Added Successfully
                                </div>');
                                $curr_url = current_url();
                                redirect($curr_url);
                        
                }
            }

           $data["business_service"] = $this->business_model->get_doctor_service($id);
           $data['bus_id']=$bus_id;
            $this->load->view("business/doctor/doctor_service1",$data);
        }
         else
        {
            redirect('admin');
        }
    }	
       public function edit_service($id)
	   {
	   if(_is_user_login($this))
       {

           $service = $this->business_model->get_business_service_by_id($id);
            $data["error"] = "";
            if($_POST)
            {
                $this->load->library('form_validation');
               $this->form_validation->set_rules('bus_title', 'Busoness Service Title', 'trim|required');
                /*$this->form_validation->set_rules('bus_price', 'Business Service Price', 'trim|required|numeric');
                $this->form_validation->set_rules('service_tax', 'Service Tax', 'trim|numeric');
                $this->form_validation->set_rules('total_tax', 'Total Tax', 'trim|numeric');*/
                
                if ($this->form_validation->run() == FALSE)
        		{
        			 if($this->form_validation->error_string()!=""){
        			  $data["error"] = '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>';
					}
        		}
        		else
        		{
                        $bus_title = $this->input->post("bus_title");
                        $bus_price = $this->input->post("bus_price");
                        $bus_time = $this->input->post("bus_time");
                        $service_tax = $this->input->post("service_tax");
                        $total_cost = $this->input->post("total_cost");
                        
                        $update_array = array(
                                "service_title"=>$bus_title,
                                /*"business_approxtime"=>$bus_time,
                                "service_price"=>$bus_price,
                                "service_tax"=>$service_tax,
                                "total_cost"=>$total_cost*/
                                );
                    

                            $this->common_model->data_update("business_services",$update_array,array("id"=>$id)
                                );
                            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Service Update Successfully
                                </div>');
                                redirect("business/business_service/".$service->bus_id);
               	}
            }

           $data["business_service"] = $this->business_model->get_business_service_by_id($id);
	   	   $this->load->view('business/edit_service',$data);
        }
        else
        {
            redirect('admin');
        }
	}
       function delete_business_service($service_id){
        if(_is_user_login($this)){
        $data = array();

            $service  = $this->business_model->get_business_service_by_id($service_id);
           if($service){
                $this->db->query("Delete from business_services where id = '".$service->id."'");
                $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Clinic Service Delete Successfully
                                </div>');
                redirect("business/business_service/".$service->bus_id);
           }
        }
        else
	    {
			redirect('admin');
	    } 
    }
/* business review area */

      function business_review($business_id){
         if(_is_user_login($this))
       {
        $data = array();

           $data["business_review"]  = $this->business_model->get_business_review($business_id);
           $this->load->view('business/business_review',$data);
       } else
        {
            redirect('admin');
        }
    }
      function doctor_review($bus_id,$doct_id){
         if(_is_user_login($this))
       {
        $data = array();

           $data["business_review"]  = $this->business_model->get_doctor_review($doct_id);
           $data['bus_id']=$bus_id;
		   $data['back']='business/list_doctor/'.$bus_id;
           $this->load->view('business/doctor/doctor_review1',$data);
       } else
        {
            redirect('admin');
        }
    }	
      function delete_business_review($service_id){
          if(_is_user_login($this)){  
            $data = array();

            $service  = $this->business_model->get_business_review_by_id($service_id);
            if($service){
                $this->db->query("Delete from business_reviews where id = '".$service->id."'");
                $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Business Service Delete Successfully
                                </div>');
                redirect("business/business_review/".$service->bus_id);
            }
           
          } 
    }
  
  /* business Photo */
      public function business_photo($id){
        if(_is_user_login($this)){
            $data = array();
            
            if($_POST){
                $this->load->library('form_validation');
                
                 $this->form_validation->set_rules('photo_title', 'Business Photo Name', 'trim|required');
                    if (empty($_FILES['bus_img']['name']))
                {
                    $this->form_validation->set_rules('bus_img', 'Business Image', 'required');
                } 
                
                if ($this->form_validation->run() == FALSE) 
        		{
        		   if($this->form_validation->error_string()!=""){
        			$data["error"] = '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                </div>';
					}
                    
        		}else
                {
                         
                        $photo_title = $this->input->post("photo_title");
                        if($_FILES["bus_img"]["size"] > 0){
                    $config['upload_path']          = './uploads/business/businessphoto/';
                    $config['allowed_types']        = 'gif|jpg|png|jpeg';
                    $this->load->library('upload', $config);
    
                    if ( ! $this->upload->do_upload('bus_img'))
                    {
                            $error = array('error' => $this->upload->display_errors());
                    }
                    else
                    {
                        $img_data = $this->upload->data();
                        $savebus["photo_image"]=$img_data['file_name'];
                         $savebus["photo_title"]=$photo_title;
                         $savebus["bus_id"]=$id;
                    }
                    
               }

                $this->common_model->data_insert("business_photo",$savebus);
                            
                            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Business Photo Added Successfully
                                </div>');
                                $curr_url = current_url();
                                redirect($curr_url);
                        
                }
            }

           $data["business_photo"] = $this->business_model->get_business_photo($id);
           $data['bus_id']=$id;
            $this->load->view("business/business_photo",$data);
        }
         else
        {
            redirect('admin');
        }
    } 
 
      public function edit_photo($id){
        if(_is_user_login($this)){
            $data = array("error"=>"");  

             $service = $this->business_model->get_business_photo_by_id($id);
            $data["setbuss"] = $this->business_model->get_business_photo_by_id($id);
            
                $this->load->library('form_validation');
                 $this->form_validation->set_rules('photo_title', 'Business Photo Name', 'trim|required');
                   
              if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
  		            
                $savebus = array(
                "photo_title"=>$this->input->post("photo_title")
                            );
            
                if($_FILES["bus_img"]["size"] > 0){
                    $config['upload_path']          = './uploads/business/businessphoto/';
                    $config['allowed_types']        = 'gif|jpg|png|jpeg';
                    $this->load->library('upload', $config);
    
                    if ( ! $this->upload->do_upload('bus_img'))
                    {
                            $error = array('error' => $this->upload->display_errors());
                    }
                    else
                    {
                        $img_data = $this->upload->data();
                        $savebus["photo_image"]=$img_data['file_name'];
                    }
                    
               }
                      
                    $this->db->update("business_photo",$savebus,array("id"=>$id)); 
                    
                    $this->session->set_flashdata("message",'<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Your Business Photo Upadete Successfully...
                                    </div>');
                                     redirect("business/business_photo/".$service->bus_id);
                    //redirect('admin/business/');
                    //redirect('admin/business/');
               	}
            
	       $data["error"] = "";
	       
           $this->load->view('business/edit_photo',$data);
        }
        else
        {
            redirect('admin');
        }
        
    }
 
      function delete_business_photo($service_id){
          if(_is_user_login($this)){  
            $data = array();

            $service  = $this->business_model->get_business_photo_by_id($service_id);
            if($service){
                $this->db->query("Delete from business_photo where id = '".$service->id."'");
                unlink("uploads/business/businessphoto/".$service->photo_image);
                $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Business Photo Delete Successfully
                                </div>');
                redirect("business/business_photo/".$service->bus_id);
            }
           
          }
          else
	    {
			redirect('admin');
	    } 
    }
 
 /* business appointment area */
        function today_business_appointment($bus_id){
            if(_is_user_login($this))
            {
                $data = array();
                $from_date = "";
                $to_date = "";
                if($this->input->post("date_range")){
                    $date_range =  $this->input->post("date_range");
                    $date_range = explode(',',$date_range);
                    $from_date = trim($date_range[0]);
                    $to_date = trim($date_range[1]);    
                }
                $data['date_range_lable'] = $this->input->post('date_range_lable');
                $user_id = "";
                $usertype = _get_current_user_type_id($this);
                if($usertype == 3 || $usertype == "3" ){
                    $user_id = _get_current_user_id($this);
                }
                $data["business"]  = $this->business_model->today_business_appointment($bus_id);
				$data["back"]="business/dashboard";
               //print_r($data);die;
                $this->load->view('business/business_appointment',$data);
            } else
            {
                redirect('admin');
            }
    } 
        function business_appointment($business_id="",$doct_id=""){
            if(_is_user_login($this))
            {
                $data = array();
                        $from_date = "";
                $to_date = "";
                if($this->input->post("date_range")){
                    $date_range =  $this->input->post("date_range");
                    $date_range = explode(',',$date_range);
                    $from_date = trim($date_range[0]);
                    $to_date = trim($date_range[1]);    
                }
                $data['date_range_lable'] = $this->input->post('date_range_lable');
                
                $user_id = "";
                $usertype = _get_current_user_type_id($this);
                
                if($usertype == 3 || $usertype == "3" ){
                    $user_id = _get_current_user_id($this);
                }

                if($doct_id == ""){
                    $doct_id = $this->input->post("filter_doct");
                    $data["doctors"] = $this->business_model->get_businesses_doctor($business_id);
                }
                $data["business"]  = $this->business_model->get_business_appointment($business_id,$user_id,$from_date,$to_date,$doct_id);
				$data["back"]="admin/doctor_list/".$business_id;
               //print_r($data);die;
                $this->load->view('business/business_appointment',$data);
            } else
            {
                redirect('admin');
            }
    }
        function patient_list(){
            if(_is_user_login($this))
            {
            	$user_id = _get_current_user_id($this);
            	$q= $this->db->query('select bus_id from business where user_id = '.$user_id);
            	$bus_id=$q->row()->bus_id;
            	//print_r($bus_id);die;
               $data = array();
               $data["appointment"] = $this->business_model->get_business_appointment_by_bus($bus_id);
            
              $this->load->view('business/patient/patient_list',$data);
            } 
            else
            {
                redirect('admin');
            }
    } 
        public function view_patient1($app_id)
     {
     	 if(_is_user_login($this))
       {
       		$doct_id = _get_current_user_id($this);
            $data = array();
            $q=$this->db->query('select * from business_appointment where id = '.$app_id)->row();
            //print_r($q);die;
            $data['user']=$this->doctor_model->get_user($q->user_id);
            $data["appointment"] = $this->business_model->get_appointment_billing_details($app_id);
             
            $data["doctor"] = $this->business_model->get_businesses_doctor_by_id($doct_id);
            $data['doct_id']=$doct_id;
           //print_r($data["appointment"]);die;
            $this->load->view('business/patient/details_view',$data);
       } else
       {
            redirect('admin');
       }
	 }
	 
	  public function view_patient_details($app_id){
        if(_is_user_login($this)){
            $data = array();
            $data['user'] = $this->users_model->get_patient_details($app_id);
            
            $this->load->view("business/patient/view_user",$data);
           }
           else
           {
		   	redirect('admin');
		   }
        }
        public function view_patient($user_id)
        {
     	 if(_is_user_login($this))
       {
       		$bus_user_id = _get_current_user_id($this);
            $q= $this->db->query('select bus_id from business where user_id = '.$bus_user_id);
            $bus_id=$q->row()->bus_id;
            $data = array();
            $data['user']=$this->doctor_model->get_user($user_id);
            $data["appointment"] = $this->business_model->get_business_appointment_for_patient($user_id,$bus_id);
              //print_r($data);die;
           
            $this->load->view('business/patient/patient_details',$data);
       } else
       {
            redirect('admin');
       }
	 }

        function appointment_details($business_id="",$doct_id=""){
            if(_is_user_login($this))
            {
                $data = array();
                        $from_date = "";
                $to_date = "";
                if($this->input->post("date_range")){
                    $date_range =  $this->input->post("date_range");
                    $date_range = explode(',',$date_range);
                    $from_date = trim($date_range[0]);
                    $to_date = trim($date_range[1]);    
                }
                $data['date_range_lable'] = $this->input->post('date_range_lable');
                
                $user_id = "";
                $usertype = _get_current_user_type_id($this);
                
                if($usertype == 3 || $usertype == "3" ){
                    $user_id = _get_current_user_id($this);
                }

                if($doct_id == ""){
                    $doct_id = $this->input->post("filter_doct");
                    $data["doctors"] = $this->business_model->get_businesses_doctor($business_id);
                }
                $data["business"]  = $this->business_model->get_business_appointment($business_id,$user_id,$from_date,$to_date,$doct_id);
              // print_r($data);die;
                $this->load->view('business/appointment_details',$data);
            } else
            {
                redirect('admin');
            }
    }
        function medicine_report()
          {
	   	if(_is_user_login($this))
            {
                $data = array();
               $data["medicine"]  = $this->business_model->get_medicine();
              //print_r($data);die;
                $this->load->view('business/medicine_report',$data);
            } else
            {
                redirect('admin');
            }
	   }
		
		 function view_medicine($id)
         {
	   	if(_is_user_login($this))
            {
                $data = array();
               $data["medicine"]  = $this->business_model->get_medicine_report($id);
               //print_r($data);die;
                $this->load->view('business/medicine_view',$data);
            } else
            {
                redirect('admin');
            }
	   }
 
         function delete_patient($id) 
         {
	   	if(_is_user_login($this))
            {
                $this->db->where('id',$id);
                $res=$this->db->delete('business_appointment');
                //print_r($res);die;
                if($res > 0)
                {
                	$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Appointment Delete Successfully..
                                </div>');
			       redirect('business/patient_list');
			    }
			  else{
			  	    $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong>Fail To Delete Appointment..
                                </div>');
			  	    redirect('business/patient_list');
			  }
            }  
        else
	    {
			redirect('admin');
	    } 
	   }		
		 function delete_medicine($id)
         {
	   	if(_is_user_login($this))
            {
                $res=$this->business_model->delete_medicine($id);
               // print_r($res);die;
                if($res > 0)
                {
                	$this->session->set_flashdata("Success", 'Successfully Deleted !');
			       redirect('business/medicine_report');
			    }
			  else{
			  	    $this->session->set_flashdata("fail", 'Fail to delete.');
			  }
            } 
            else
            {
                redirect('admin');
            }
	   }
	     function change_status($id = NULL)
	     {

		$query = $this->business_model->get_where($id);

		$result = $query->row();
		
		if($result->status == 1)
		{
			$data = array('status'=>'0');
			$this->session->set_flashdata('Success', 'deactivated Successfully');
		}

		else if($result->status == 0)
		{
			$data = array('status'=>'1');	
			$this->session->set_flashdata('Success', 'activated Successfully');
		}

			$this->business_model->change_status($id, $data);
			redirect('business/medicine_report');

	}

		
	   function patient_action($business_id="",$doct_id=""){
            if(_is_user_login($this))
            {
                $data = array();
                        $from_date = "";
                $to_date = "";
                if($this->input->post("date_range")){
                    $date_range =  $this->input->post("date_range");
                    $date_range = explode(',',$date_range);
                    $from_date = trim($date_range[0]);
                    $to_date = trim($date_range[1]);    
                }
                $data['date_range_lable'] = $this->input->post('date_range_lable');
                
                $user_id = "";
                $usertype = _get_current_user_type_id($this);
                
                if($usertype == 3 || $usertype == "3" ){
                    $user_id = _get_current_user_id($this);
                }

                if($doct_id == ""){
                    $doct_id = $this->input->post("filter_doct");
                    $data["doctors"] = $this->business_model->get_businesses_doctor();
                }
                $data["business"]  = $this->business_model->get_business_appointment($business_id,$user_id,$from_date,$to_date,$doct_id);
                //print_r($data);die;
                $this->load->view('business/patient_action',$data);
            } else
            {
                redirect('admin');
            }
    }
       function delete_business_appointment($service_id){
           if(_is_user_login($this)){ 
                $data = array();

                $service  = $this->business_model->get_business_appointment_by_id($service_id);
                if($service){
                    $this->db->query("Delete from business_appointment where id = '".$service->id."'");
                    $this->db->query("Delete from business_appointment_services where busness_appointment_id = '".$service->id."'");
                    $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Appointment Delete Successfully
                                </div>');
                    redirect("business/business_appointment/".$service->bus_id);
                }
            }
           else
            {
                redirect('admin');
            }
    }   
   
       function delete_business_appointment_from_today($service_id){
           if(_is_user_login($this)){ 
                $data = array();

                $service  = $this->business_model->get_business_appointment_by_id($service_id);
                if($service){
                    $this->db->query("Delete from business_appointment where id = '".$service->id."'");
                    $this->db->query("Delete from business_appointment_services where busness_appointment_id = '".$service->id."'");
                    $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Appointment Delete Successfully
                                </div>');
                    redirect("business/today_business_appointment/".$service->bus_id);
                }
            }
           else
            {
                redirect('admin');
            }
    }   
       function delete_appointment($id){
       //	print($id);die;
           if(_is_user_login($this)){ 
           $user_id = _get_current_user_id($this);
           $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
           $this->db->query("Delete from business_appointment where id = '".$id."'");
          $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Appointment Delete Successfully
                                </div>');
                    redirect("business/dashboard");
            }
           else
            {
                redirect('admin');
            }
    }   

       function delete_appointment_from_appointment_list($id){
       //	print($id);die;
           if(_is_user_login($this)){ 
           $user_id = _get_current_user_id($this);
           $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
           $this->db->query("Delete from business_appointment where id = '".$id."'");
          $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Appointment Delete Successfully
                                </div>');
                    redirect("business/appointment_details/",$bus_id);
            }
           else
            {
                redirect('admin');
            }
    }   
    /* business appointment area */

     function appointment_service($business_id){
        if(_is_user_login($this))
       {
            $data = array();
            $data["appointment"] = $this->business_model->get_business_appointment_by_id($business_id);
            $data["doctor"] = $this->business_model->get_businesses_doctor_by_id($data["appointment"]->doct_id);
            $data["business_appo"]  = $this->business_model->get_business_appointment_service($business_id);
            $this->load->view('business/appointment_service',$data);
       } else
       {
            redirect('admin');
       }
    }
	
	
	/* public function edit_appointment_service($bus_id,$id){
	 	 if(_is_user_login($this)){
             $doct_id = $this->input->post("doct_filter");
             $appointments  = $this->business_model->get_business_appointment($bus_id,"","","",$doct_id);
             $array = array();
             foreach($appointments as $app){
                 $total_expand_time =  explode(':',$app->taken_time);
                $total_expand_time_add = '+'.$total_expand_time[0].' hour +'.$total_expand_time[1].' minutes';
                                
                 $endTime = strtotime($total_expand_time_add, strtotime($app->start_time));
                $time_slot = array("title"=>$app->app_name,
                "start"=>$app->appointment_date."T".$app->start_time,
                "end"=>$app->appointment_date."T".date('H:i:s', $endTime),
                "allDay"=>false,
                "url"=>"javascript:onEvenClick('".$app->id."');");
                if($app->status == 0){
                    $time_slot["backgroundColor"] = "#ccc";
                    $time_slot["borderColor"] = "#ccc";
    
                }else if ($app->status == 1){
                    $time_slot["backgroundColor"] = "#00a65a";
                    $time_slot["borderColor"] = "#00a65a";
    
                }
                $array[] = $time_slot;
            }
         
         $data["appointments"] = $array;
         $data["bus_id"] = $bus_id;
         $data["services"] = $this->business_model->get_business_service($bus_id); 
         $data["doctors"] = $this->business_model->get_all_businesses_doctor();
         $data["appointment"] = $this->business_model->get_business_appointment_by_id($id);
            $data["doctor"] = $this->business_model->get_businesses_doctor_by_id($data["appointment"]->doct_id);
            $data["business_appo"]  = $this->business_model->get_business_appointment_service($id);
            //print_r($data["business_appo"]);die;
         $this->load->view('business/edit_appointment_service',$data);
        }
      
    }
	*/
    function delete_business_appointment_service($service_id){
           if(_is_user_login($this)){ 
                $data = array();

                $service  = $this->business_model->get_business_appointment_service($service_id);
                if($service){
                    $this->db->query("Delete from business_appointment where id = '".$service->id."'");
                    $this->db->query("Delete from business_appointment_services where busness_appointment_id = '".$service->id."'");
                    $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Business Appointment Delete Successfully
                                    </div>');
                    redirect("business/business_appointment/".$service->bus_id);
               }
           }
           else
            {
                redirect('admin');
            }
    }   
           
    function business_schedule($bus_id){
        if(_is_user_login($this)){
            $data = array();
            if($_POST){
                    $this->load->library('form_validation');
                    $this->form_validation->set_rules('morning_from', 'Morning From', 'trim|required');
                    $this->form_validation->set_rules('morning_to', 'Morning To', 'trim|required');
                    $this->form_validation->set_rules('afternoon_from', 'Afternoon From', 'trim|required');
                    $this->form_validation->set_rules('afternoon_to', 'Afternoon To', 'trim|required');
                    $this->form_validation->set_rules('evening_from', 'Evening From', 'trim|required');
                    $this->form_validation->set_rules('evening_to', 'Evening To', 'trim|required');
                    $this->form_validation->set_rules('morning_interval', 'Morning Interval', 'trim|required');
                    $this->form_validation->set_rules('evening_interval', 'Evening Interval', 'trim|required');
                    $this->form_validation->set_rules('afternoon_interval', 'Afternoon Interval', 'trim|required');   
                    if ($this->form_validation->run() == FALSE)
            		{
      		            if($this->form_validation->error_string()!=""){
            			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                            <i class="fa fa-warning"></i>
                                          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                          <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                        </div>');
                        }
            		}
            		else
            		{
          		         $morning_from = date("H:i:s", strtotime($this->input->post("morning_from")));
                           $morning_to = date("H:i:s", strtotime($this->input->post("morning_to")));
                           $evening_from = date("H:i:s", strtotime($this->input->post("evening_from")));
                           $evening_to = date("H:i:s", strtotime($this->input->post("evening_to")));
                           $afternoon_from = date("H:i:s", strtotime($this->input->post("afternoon_from")));
                           $afternoon_to = date("H:i:s", strtotime($this->input->post("afternoon_to")));
                           $morning_interval = $this->input->post("morning_interval");
                           $afternoon_interval = $this->input->post("afternoon_interval");
                           $evening_interval = $this->input->post("evening_interval");
                           $book_type = "slot";//$this->input->post("book_type");
                           $days = implode(',',$_REQUEST['day']);
                           
                            $sql = $this->db->insert_string("business_appointment_schedule",
                           array("bus_id"=>$bus_id,
                           "working_days"=>$days,
                           "morning_time_start"=>$morning_from,
                           "morning_time_end"=>$morning_to,
                           "morning_tokens"=>$morning_interval,
                           "afternoon_time_start"=>$afternoon_from,
                           "afternoon_time_end"=>$afternoon_to,
                           "afternoon_tokens"=>$afternoon_interval,
                           "evening_time_start"=>$evening_from,
                           "evening_time_end"=>$evening_to,
                           "evening_tokens"=>$evening_interval,"book_type"=>$book_type)) . " ON DUPLICATE KEY UPDATE  working_days= '".$days."', ".
                           "morning_time_start = '".$morning_from."', ".
                           "morning_time_end = '".$morning_to."', ".
                           "morning_tokens = '".$morning_interval."', ".
                           "afternoon_time_start = '".$afternoon_from."', ".
                           "afternoon_time_end = '".$afternoon_to."', ".
                           "afternoon_tokens = '".$afternoon_interval."', ".
                           "evening_time_start = '".$evening_from."', ".
                           "evening_time_end = '".$evening_to."', ".
                           "evening_tokens = '".$evening_interval."', ".
                           "book_type = '".$book_type."'";
    $this->db->query($sql);
    $id = $this->db->insert_id();
                            
                             $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                            <i class="fa fa-Success"></i>
                                          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                          <strong>Success!</strong> Time slot added Successfully
                                        </div>');
                              
                    }
                
        }

        $schedule = $this->business_model->get_business_schedule($bus_id);
        $data["schedule"] = $schedule;
        $data["bus_id"] = $bus_id;
        $this->load->view("business/business_schedule",$data);
        
        }
        else
            {
                redirect('admin');
            }   
    }  
    function doctor_schedule($bus_id,$doct_id){
        if(_is_user_login($this)){
            $data = array();
            if($_POST){
                    $this->load->library('form_validation');
                    $this->form_validation->set_rules('morning_from', 'Morning From', 'trim|required');
                    $this->form_validation->set_rules('morning_to', 'Morning To', 'trim|required');
                    $this->form_validation->set_rules('afternoon_from', 'Afternoon From', 'trim|required');
                    $this->form_validation->set_rules('afternoon_to', 'Afternoon To', 'trim|required');
                    $this->form_validation->set_rules('evening_from', 'Evening From', 'trim|required');
                    $this->form_validation->set_rules('evening_to', 'Evening To', 'trim|required');
                    $this->form_validation->set_rules('morning_interval', 'Morning Interval', 'trim|required');
                    $this->form_validation->set_rules('evening_interval', 'Evening Interval', 'trim|required');
                    $this->form_validation->set_rules('afternoon_interval', 'Afternoon Interval', 'trim|required');   
                    if ($this->form_validation->run() == FALSE)
            		{
      		            if($this->form_validation->error_string()!=""){
            			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                            <i class="fa fa-warning"></i>
                                          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                          <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                        </div>');
                        }
            		}
            		else
            		{
          		         $morning_from = date("H:i:s", strtotime($this->input->post("morning_from")));
                           $morning_to = date("H:i:s", strtotime($this->input->post("morning_to")));
                           $evening_from = date("H:i:s", strtotime($this->input->post("evening_from")));
                           $evening_to = date("H:i:s", strtotime($this->input->post("evening_to")));
                           $afternoon_from = date("H:i:s", strtotime($this->input->post("afternoon_from")));
                           $afternoon_to = date("H:i:s", strtotime($this->input->post("afternoon_to")));
                           $morning_interval = $this->input->post("morning_interval");
                           $afternoon_interval = $this->input->post("afternoon_interval");
                           $evening_interval = $this->input->post("evening_interval");
                           $book_type = "slot";//$this->input->post("book_type");
                           $days = implode(',',$_REQUEST['day']);
                           
                            $sql = $this->db->insert_string("business_appointment_schedule",
                           array("doct_id"=>$doct_id,
                           "working_days"=>$days,
                           "morning_time_start"=>$morning_from,
                           "morning_time_end"=>$morning_to,
                           "morning_tokens"=>$morning_interval,
                           "afternoon_time_start"=>$afternoon_from,
                           "afternoon_time_end"=>$afternoon_to,
                           "afternoon_tokens"=>$afternoon_interval,
                           "evening_time_start"=>$evening_from,
                           "evening_time_end"=>$evening_to,
                           "evening_tokens"=>$evening_interval,"book_type"=>$book_type)) . " ON DUPLICATE KEY UPDATE  working_days= '".$days."', ".
                           "morning_time_start = '".$morning_from."', ".
                           "morning_time_end = '".$morning_to."', ".
                           "morning_tokens = '".$morning_interval."', ".
                           "afternoon_time_start = '".$afternoon_from."', ".
                           "afternoon_time_end = '".$afternoon_to."', ".
                           "afternoon_tokens = '".$afternoon_interval."', ".
                           "evening_time_start = '".$evening_from."', ".
                           "evening_time_end = '".$evening_to."', ".
                           "evening_tokens = '".$evening_interval."', ".
                           "book_type = '".$book_type."'";
    $this->db->query($sql);
    $id = $this->db->insert_id();
                            
                             $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                            <i class="fa fa-success"></i>
                                          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                          <strong>Success!</strong> Time slot added Successfully
                                        </div>');
                              
                    }
                
        }

        $schedule = $this->business_model->get_doctor_schedule($doct_id);
        $data["schedule"] = $schedule;
        $data['bus_id']=$bus_id;
        //print_r($data);die;
        $this->load->view("business/doctor/doctor_schedule1",$data);
        
        }
        else
            {
                redirect('admin');
            }
    } 

    /* public function books($bus_id=""){
        if(_is_user_login($this)){
             $doct_id = $this->input->post("doct_filter");
             $appointments  = $this->business_model->get_business_appointment($bus_id,"","","",$doct_id);
             $array = array();
             foreach($appointments as $app){
                 $total_expand_time =  explode(':',$app->taken_time);
                $total_expand_time_add = '+'.$total_expand_time[0].' hour +'.$total_expand_time[1].' minutes';
                                
                 $endTime = strtotime($total_expand_time_add, strtotime($app->start_time));
                $time_slot = array("title"=>$app->app_name,
                "start"=>$app->appointment_date."T".$app->start_time,
                "end"=>$app->appointment_date."T".date('H:i:s', $endTime),
                "allDay"=>false,
                "url"=>"javascript:onEvenClick('".$app->id."');");
                if($app->status == 0){
                    $time_slot["backgroundColor"] = "#e98080";
                    $time_slot["borderColor"] = "#e98080";
    
                }else if ($app->status == 1){
                    $time_slot["backgroundColor"] = "#00a65a";
                    $time_slot["borderColor"] = "#00a65a";
    
                }
                $array[] = $time_slot;
            }
         
         $data["appointments"] = $array;
         $data["bus_id"] = $bus_id;
         $data["services"] = $this->business_model->get_business_service($bus_id); 
         $data["doctors"] = $this->business_model->get_businesses_doctor($bus_id); 
         $data["merge_doctors"] = $this->business_model->get_merge_businesses_doctor($bus_id);
         $data["app"]=$this->business_model->get_business_appointment1($bus_id);
         //$data['schedule']=$this->doctor_model->get_doctor_schedule
         //print_r($data["app"]);die;  
         $this->load->view("business/books",$data);
        
        }
        else
            {
                redirect('admin');
            }
    }*/
    
    
    public function get_schedule_slot(){
        //header('Content-type: text/json');
        $date =  date("Y-m-d",strtotime($this->input->post("start_date")));
        $time_slots_date_array = array();  
		//print_r($this->input->post("bus_id"));
		//print_r($this->input->post("doct_id"));
		//print_r($this->input->post("start_date"));
        $time_slots_date_array = $this->business_model->get_time_slot($date,$this->input->post("bus_id"),$this->input->post("doct_id"));
        if(!empty($time_slots_date_array)){
            $this->load->view("business/timeslot",array("timeslot"=>$time_slots_date_array,"date"=>$date));
        }else{
            echo "No time schedule setup";
        }
        //echo json_encode($time_slots_date_array);
                
    }
   
    public function get_time_slot(){
                $data = array();
                $this->load->library('form_validation');
                
                $this->form_validation->set_rules('bus_id', 'Business ID', 'trim|required');
                $this->form_validation->set_rules('times_slot', 'Total Time', 'trim|required');
                if ($this->form_validation->run() == FALSE) 
        		{
                        $data["responce"] = false;       
  			           $data["error"] = $this->form_validation->error_string();
                    
        		}else
                {
                        $bus_id = $this->input->post("bus_id");
                        $time_slots = explode(',', $this->input->post("times_slot") );
                        $date = $this->input->post("date");
                        $total_time = trim($time_slots[0]);
                        for($i = 1; $i < count($time_slots); $i++){
                            $time_slots[$i];
                            $total_expand_time =  explode(':',trim($time_slots[$i]));
                            $total_expand_time_add = '+'.$total_expand_time[0].' hour +'.$total_expand_time[1].' minutes';
                            $total_time = date("H:i:s",strtotime($total_expand_time_add, strtotime($total_time)));
                        }

                        $data = $this->business_model->get_time_slot($bus_id,$total_time, $date);
                        $data["date"] = $date;
                        $this->load->view("business/get_time_slot",$data);
                }

    }
    public function add_appointment(){
        if(_is_user_login($this)){
                
                $this->load->library('form_validation');
                $this->form_validation->set_rules('doct_id', 'Doct ID', 'trim|required');
                $this->form_validation->set_rules('bus_id', 'Business ID', 'trim|required|callback_check_appointment');
                $this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
                $this->form_validation->set_rules('app_name', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('email', 'Email Id', 'trim|required|valid_email');
                $this->form_validation->set_rules('phone', 'Contact No', 'trim|required|integer|exact_length[10]');
                $this->form_validation->set_rules('appointment_date', 'App date', 'trim|required');
                $this->form_validation->set_rules('start_time', 'Start Time', 'trim|required');
                $this->form_validation->set_rules('time_token', 'Time Token', 'trim|required');
                if($this->input->post('d_reg_proof') != '' )
                {
					$this->form_validation->set_rules('d_reg_proof', 'Patient Image', 'trim|callback_validate_patient_image');
				}
				if($this->input->post("chkmember") != "on")
				{
					$this->form_validation->set_rules('member_name', 'Member Name', 'trim|required');
					$this->form_validation->set_rules('msalutations', 'Member Salutation', 'trim|required');
				}
                $this->form_validation->set_error_delimiters('', '');
                $bus_id = _get_current_bus_id($this);
                if ($this->form_validation->run() == FALSE) 
        		{
 
                        if($this->form_validation->error_string() != '')
                        {
							$this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
						}
                               
  			            //$data["error"] = $this->form_validation->error_string();
        		}else
                {
                	//print_r($this->input->post("msalutations"));die;
                	$doct_id = $this->input->post("doct_id");
                	$user_id = $this->input->post("user_id");
                	$chkmember = $this->input->post("chkmember");
                	$appointment_date = $this->input->post("appointment_date");
                	$start_time = $this->input->post("start_time");
                	$time_token = $this->input->post("time_token");
                	$data = array();
                	$app_type="";
                	$user_type_id="";
                	$user_image='';
                	$doct_schedule=$this->db->select('*')->from('business_appointment_schedule')->where('doct_id',$doct_id)->get()->result();
                	if(!empty($doct_schedule))
                	{
                		$q = $this->doctor_model->check_patient_appointment($this->input->post("email"),$this->input->post("phone"));
                	     //print_r($q);die;
                         if(!empty($q))
                         {
						 	$app_type="Repeated";
						 	$user_type_id=$q['0']['user_type_id'];
						 }
						 else{
						 	
						 	$app_type="First visit";
						 	$user_type_id='1';
						 }
                		if(!empty($_FILES['d_reg_proof']['name']))
                	   {
					 	if($_FILES["d_reg_proof"]["size"] > 0){
                        $config['upload_path']          = './uploads/patient/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        //$config['max_width']        = '400';
                        //$config['max_height'] = '400';
                        //$this->load->library('upload', $config);
                        $this->load->library('upload');
                        $this->upload->initialize($config);
                        if (!$this->upload->do_upload('d_reg_proof'))
                        {     
                                 $data["responce"] = false;       
  			                     $data["error"] = array('error' => $this->upload->display_errors());
  			                     
  			                     //print_r($data["error"]);
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $user_image=$img_data['file_name'];
                        }
                        }
					    }
        			  else
                        {
							$user_image=$this->input->post("d_reg_proof1");
						}
						//print_r($user_image);die;
						 $dob=$this->input->post("user_bdate");
    					 $diff = (date('Y') - date('Y',strtotime($dob)));
    					 //print_r($_POST['msalutations']);die;
                		$sql = $this->db->insert_string("users",
                           array(
                           "user_fullname"=>$this->input->post("app_name"),
                           "user_email"=>$this->input->post("email"),
                           "user_phone"=>$this->input->post("phone"),
                           "salutations"=>$this->input->post("salutations"),
                           "gender"=>$this->input->post("u_gender"),
                           "user_type_id"=>$user_type_id,
                           "user_bdate"=>date('Y-m-d',strtotime($dob)),
                           "user_age"=>$diff,
                           "user_image"=>$user_image
                           )) . " ON DUPLICATE KEY UPDATE  
                           user_fullname= '".$this->input->post("app_name")."', ".
                           "user_email = '".$this->input->post("email")."', ".
                           "user_phone = '".$this->input->post("phone")."', ".
                           "salutations = '".$this->input->post("salutations")."', ".
                           "gender = '".$this->input->post("u_gender")."', ".
                           "user_type_id = '".$user_type_id."', ".
                           "user_bdate = '".date('Y-m-d',strtotime($dob))."', ".
                           "user_age = '".$diff."', ".
                           "user_image = '".$user_image."'";
                           $this->db->query($sql);
                           $p = $this->db->select('*')->from('users')->where('user_email',$this->input->post("email"))->where('user_phone',$this->input->post("phone"))->get()->row();
                		if($chkmember == "on")
                		{
						  
                	$update=array(
                	    "bus_id"=>$bus_id,
                        "doct_id"=>$doct_id,
                        "user_id"=>$p->user_id,
                        "appointment_date"=>date("Y-m-d",strtotime($appointment_date)),
                        "start_time"=>date("H:i:s",strtotime($start_time)),
                        "time_token"=>$time_token,
                        "date_time"=>date("Y-m-d",strtotime($appointment_date)).' '.date("H:i:s",strtotime($start_time)),
                        "mode_app"=>$this->input->post("mode_app"),
                        "app_type"=>$app_type);
                       
                        $this->db->insert("business_appointment",$update);
                        $app_id = $this->db->insert_id();
                       
                        $bus_title=$this->db->select('bus_title')->from('business')->where('bus_id',$bus_id)->get()->row()->bus_title;
                        $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>$doct_id,
                        'notification'=>$this->input->post("app_name")." has booked an appointment with you at ".date('Y-m-d H:i:s').".",
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s'));
                        $n_id=$this->db->insert('doctor_notification',$notification);
                        $data["responce"] = true;
                        $appointment = $this->db->query("Select * from business_appointment where id = '".$app_id."' limit 1");
                        $data["data"] = $appointment->row();
                        if($app_id > 0 )
                        {
                        	$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Add Appointment Successfully.</div>');
							redirect('business/add_appointment');
						}
						else
						{
							$this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong>Fail to Add Appointment.</div>');
							 redirect('business/add_appointment');
						}	
					 }
					 else
					 {
					 	$p = $this->db->select('*')->from('users')->where('user_email',$this->input->post("email"))->where('user_phone',$this->input->post("phone"))->get()->row();
					 	$q = $this->db->select('*')->from('user_member')->where('user_id',$p->user_id)->where('m_name',$this->input->post("member_name"))->get()->row();
                         if(!empty($q))
                         {
						 	$app_type="Repeated";
						 }
						 else{
						 	
						 	$app_type="First visit";
						 }
                		
						 $dob=$this->input->post("member_bdate");
    					 $diff = (date('Y') - date('Y',strtotime($dob)));
                    	 $sql = $this->db->insert_string("user_member",
                           array(
                           "user_id"=>$p->user_id,
                           "salutations"=>$_POST['msalutations'],
                           "m_name"=>$this->input->post("member_name"),
                           "m_gender"=>$this->input->post("m_gender"),
                           "birth_date"=>date('d/m/Y',strtotime($dob)),
                           "relation"=>$this->input->post("relation"),
                           "alternate_number"=>$this->input->post("member_phone"),
                           "age"=>$diff
                           
                           )) . " ON DUPLICATE KEY UPDATE  
                            m_name= '".$this->input->post("member_name")."', ".
                           "user_id = '".$p->user_id."', ".
                           "salutations = '".$_POST['msalutations']."', ".
                           "m_gender = '".$this->input->post("m_gender")."', ".
                           "birth_date = '".date('d/m/Y',strtotime($dob))."', ".
                           "relation = '".$this->input->post("relation")."', ".
                           "alternate_number = '".$this->input->post("member_phone")."', ".
                           "age = '".$diff."'";
                            $this->db->query($sql);
                            $sub_user_id=$this->db->insert_id();
                           
                	$update=array(
                	    "bus_id"=>$bus_id,
                        "doct_id"=>$doct_id,
                        "user_id"=>$p->user_id,
                        "sub_user_id"=>$sub_user_id,
                        "appointment_date"=>date("Y-m-d",strtotime($appointment_date)),
                        "start_time"=>date("H:i:s",strtotime($start_time)),
                        "time_token"=>$time_token,
                        "date_time"=>date("Y-m-d",strtotime($appointment_date)).' '.date("H:i:s",strtotime($start_time)),
                        "mode_app"=>$this->input->post("mode_app"),
                        "app_type"=>$app_type);
                       
                        $this->db->insert("business_appointment",$update);
                        $app_id = $this->db->insert_id();
                        $bus_title=$this->db->select('bus_title')->from('business')->where('bus_id',$bus_id)->get()->row()->bus_title;
                        $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>$doct_id,
                        'notification'=>$this->input->post("member_name")." has booked an appointment with you at ".date('Y-m-d H:i:s').".",
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s'));
                        $n_id=$this->db->insert('doctor_notification',$notification);
                        $data["responce"] = true;
                        $appointment = $this->db->query("Select * from business_appointment where id = '".$app_id."' limit 1");
                        $data["data"] = $appointment->row();
                        if($app_id > 0 )
                        {
                        	$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Add Appointment Successfully.</div>');
							redirect('business/add_appointment');
						}
						else
						{
							$this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong>Fail to Add Appointment.</div>');
							 redirect('business/add_appointment');
						}	
					 }
					}
					else
					{
						$this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong>This Doctor Slot Not Available.</div>');
							 redirect('business/add_appointment');
					}
                	
                }
             $doct_id = $this->input->post("doct_filter");
             $appointments  = $this->business_model->get_business_appointment($bus_id,"","","",$doct_id);
             $array = array();
             $patient_name='';
             foreach($appointments as $app){
                 $total_expand_time =  explode(':',$app->taken_time);
                $total_expand_time_add = '+'.$total_expand_time[0].' hour +'.$total_expand_time[1].' minutes';
                  $endTime = strtotime($total_expand_time_add, strtotime($app->start_time));               
                 if($app->sub_user_id == '0')
                {
					$patient_name=$app->app_name;
				} 
				else
				{
					$q=$this->db->select('*')->from('user_member')->where('id',$app->sub_user_id)->get()->row();
					//print_r();die;
					$patient_name=$q->m_name;
				} 
                $time_slot = array("title"=>$patient_name,
                "start"=>$app->appointment_date."T".$app->start_time,
                "end"=>$app->appointment_date."T".date('H:i:s', $endTime),
                "allDay"=>false,
                "url"=>"javascript:onEvenClick('".$app->id."');");
                if($app->status == 0){
                    $time_slot["backgroundColor"] = "#e98080";
                    $time_slot["borderColor"] = "#e98080";
    
                }else if ($app->status == 1){
                    $time_slot["backgroundColor"] = "#00a65a";
                    $time_slot["borderColor"] = "#00a65a";
    
                }
                $array[] = $time_slot;
            }
         
         $data["appointments"] = $array;
         $data["bus_id"] = $bus_id;
         $data["services"] = $this->business_model->get_business_service($bus_id); 
         $data["doctors"] = $this->business_model->get_businesses_doctor_for_appointment($bus_id); 
         $data["merge_doctors"] = $this->business_model->get_merge_businesses_doctor($bus_id);
         $data["app"]=$this->business_model->get_business_appointment1($bus_id);
         //$data['schedule']=$this->doctor_model->get_doctor_schedule
        // print_r($data);die;  
         $this->load->view("business/books",$data);
                //echo json_encode($data); 
		}
		else
        {
                redirect('admin');
        }
    }

     public function filter_doctor()
     {
	 	$doct_id = $this->input->post("doct_filter");
	 	$bus_id = _get_current_bus_id($this);
             $appointments  = $this->business_model->get_business_appointment($bus_id,"","","",$doct_id);
             $array = array();
             foreach($appointments as $app){
                 $total_expand_time =  explode(':',$app->taken_time);
                $total_expand_time_add = '+'.$total_expand_time[0].' hour +'.$total_expand_time[1].' minutes';
                                
                 $endTime = strtotime($total_expand_time_add, strtotime($app->start_time));
                $time_slot = array("title"=>$app->app_name,
                "start"=>$app->appointment_date."T".$app->start_time,
                "end"=>$app->appointment_date."T".date('H:i:s', $endTime),
                "allDay"=>false,
                "url"=>"javascript:onEvenClick('".$app->id."');");
                if($app->status == 0){
                    $time_slot["backgroundColor"] = "#e98080";
                    $time_slot["borderColor"] = "#e98080";
    
                }else if ($app->status == 1){
                    $time_slot["backgroundColor"] = "#00a65a";
                    $time_slot["borderColor"] = "#00a65a";
    
                }
                $array[] = $time_slot;
            }
         
         $data["appointments"] = $array;
         $data["bus_id"] = $bus_id;
         $data["services"] = $this->business_model->get_business_service($bus_id); 
         $data["doctors"] = $this->business_model->get_businesses_doctor($bus_id); 
         $data["merge_doctors"] = $this->business_model->get_merge_businesses_doctor($bus_id);
         $data["app"]=$this->business_model->get_business_appointment($bus_id,"","","","");
         //$data['schedule']=$this->doctor_model->get_doctor_schedule
         //print_r($data);die;  
         $this->load->view("business/books",$data);
	 }
     
     /*public function check_appointment()
     { 
        $check = TRUE;
	 	$doct_id = $this->input->post("doct_id");
        $bus_id = $this->input->post("bus_id");
        $appointment_date = $this->input->post("appointment_date");
        $start_time = $this->input->post("start_time");
        $time_token = $this->input->post("time_token");
        //print_r(date('m/d/Y'));
        //print_r(date('H:i:s'));
        //($appointment_date);
        //print_r($start_time);die;
        $res= $this->db->select('*')->from('business_appointment')->where('doct_id',$doct_id)->where('appointment_date',date("Y-m-d",strtotime($appointment_date)))->like('start_time',date("H:i:s",strtotime($start_time)))->like('time_token',$time_token)->get()->result();
        //int_r($res);die;
        if(!empty($res))
        {
        	$this->form_validation->set_message('check_appointment', "This slot already booked.");
			$check=FALSE;
			return $check;
		}
		elseif($appointment_date == date('m/d/Y') && $start_time < date('H:i:s'))
		{
			$this->form_validation->set_message('check_appointment', "You are choose past slot.");
			$check=FALSE;
			return $check;
		}
		return $check;
	 } */
	 
    public function edit_appointment_service($id){
	 	//print_r('1');die;
	 	 if(_is_user_login($this)){
	 	 	$appointment_id=$this->input->post("appointment_id");
            $doct_id = $this->input->post("doct_id");
            $bus_id = _get_current_bus_id($this);
            $user_id = $this->input->post("user_id");
            $appointment_date = $this->input->post("appointment_date");
            $start_time = $this->input->post("start_time");
            $time_token = $this->input->post("time_token");
	 	 	$data = array();
                $this->load->library('form_validation');
                $this->form_validation->set_rules('doct_id', 'Doct ID', 'trim|required|callback_check_appointment');
                //$this->form_validation->set_rules('app_id', 'Appointment ID', 'trim|required');
                //$this->form_validation->set_rules('bus_id', 'Business ID', 'trim|required');
                //$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
                //$this->form_validation->set_rules('app_name', 'Full Name', 'trim|required');
                //$this->form_validation->set_rules('email', 'Email', 'trim|required');
                //$this->form_validation->set_rules('phone', 'Phone', 'trim|required');
                $this->form_validation->set_rules('appointment_date', 'App date', 'trim|required');
                $this->form_validation->set_rules('start_time', 'Start Time', 'trim|required');
                $this->form_validation->set_rules('time_token', 'Slot', 'trim|required');
                /*$this->form_validation->set_rules('services', 'Services', 'trim|required');*/
                //$this->form_validation->set_error_delimiters('', '');
                
                if ($this->form_validation->run() == FALSE) 
        		{  
        		        if($this->form_validation->error_string() != '')
        		        {
						    $data["responce"] = false;  
                           $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong>'.$this->form_validation->error_string()
                                .'</div>'); 
						}
                    
        		}else
                {
                 		
                        //$patient_img="";
                       $update=array(
                         "bus_id"=>$bus_id,
                         "doct_id"=>$doct_id,
                         "appointment_date"=>date("Y-m-d",strtotime($appointment_date)),
                         "start_time"=>date("H:i:s",strtotime($start_time)),
                         "time_token"=>$time_token,
                         "date_time"=>date("Y-m-d",strtotime($appointment_date)).' '.date("H:i:s",strtotime($start_time))  
                        //"app_name"=>$this->input->post("app_name"),
                        //"app_email"=>$this->input->post("email"),
                        //"app_phone"=>$this->input->post("phone")
                        );
                         //print_r($update);die;
        			 /*if($_FILES["d_reg_proof"]["size"] > 0){
                        $config['upload_path']          = './uploads/patient/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('d_reg_proof'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $update['patient_img'] = $img_data['file_name'];
                        }
                     }*/
                   
                         $this->db->where('id',$appointment_id);
                         $this->db->update("business_appointment",$update);
                         $app_id = $this->db->insert_id();
                         $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Appointment Update Successfully</div>'); 
                        //$business = $this->business_model->get_businesses_by_id($bus_id);
                        
                     /*   $service_array = explode(',',$this->input->post("services"));
                        $this->db->where('busness_appointment_id',$appointment_id);
                        $this->db->delete("business_appointment_services");
                        foreach($service_array as $service){
                        
                            $this->db->insert("business_appointment_services",array("busness_appointment_id"=>$appointment_id,
                            "busness_service_id"=>trim($service),
                            "service_qty"=>1));
                        }*/
                        
                        $data["responce"] = true;
                        $appointment = $this->db->query("Select * from business_appointment where id = '".$app_id."' limit 1");
                        $data["data"] = $appointment->row();
                }
             $doct_id = $this->input->post("doct_filter");
             $appointments  = $this->business_model->get_business_appointment($bus_id,"","","",$doct_id);
             $array = array();
             foreach($appointments as $app){
                 $total_expand_time =  explode(':',$app->taken_time);
                $total_expand_time_add = '+'.$total_expand_time[0].' hour +'.$total_expand_time[1].' minutes';
                                
                 $endTime = strtotime($total_expand_time_add, strtotime($app->start_time));
                $time_slot = array("title"=>$app->app_name,
                "start"=>$app->appointment_date."T".$app->start_time,
                "end"=>$app->appointment_date."T".date('H:i:s', $endTime),
                "allDay"=>false,
                "url"=>"javascript:onEvenClick('".$app->id."');");
                if($app->status == 0){
                    $time_slot["backgroundColor"] = "#ccc";
                    $time_slot["borderColor"] = "#ccc";
    
                }else if ($app->status == 1){
                    $time_slot["backgroundColor"] = "#00a65a";
                    $time_slot["borderColor"] = "#00a65a";
    
                }
                $array[] = $time_slot;
            }
         
         $data["appointments"] = $array;
         $data["bus_id"] = $bus_id;
         $data["services"] = $this->business_model->get_business_service($bus_id); 
         $data["doctors"] = $this->business_model->get_all_businesses_doctor();
         $data["appointment"] = $this->business_model->get_business_appointment_by_id1($id);
         //print_r($data['appointment']);die;
         $data["doctor"] = $this->business_model->get_businesses_doctor_by_id($data["appointment"]->doct_id);
         $data["business_appo"]  = $this->business_model->get_business_appointment_service($id);
            //print_r($data["business_appo"]);die;
         $this->load->view('business/edit_appointment_service',$data);
        }
         else
            {
                redirect('admin');
            }
      
    }
    
     public function check_appointment()
     { 
        $check = TRUE;
	 	$doct_id = $this->input->post("doct_id");
        $bus_id = $this->input->post("bus_id");
        $appointment_date = $this->input->post("appointment_date");
        $start_time = $this->input->post("start_time");
        $time_token = $this->input->post("time_token");
        //print_r(date('m/d/Y'));
        //print_r(date('H:i:s'));
        //($appointment_date);
        //print_r($start_time);die;
        $res= $this->db->select('*')->from('business_appointment')->where('doct_id',$doct_id)->where('appointment_date',date("Y-m-d",strtotime($appointment_date)))->like('start_time',date("H:i:s",strtotime($start_time)))->like('time_token',$time_token)->get()->result();
        //int_r($res);die;
        if(!empty($res))
        {
        	$this->form_validation->set_message('check_appointment', "This slot already booked.");
			$check=FALSE;
			return $check;
		}
		elseif($appointment_date == date('m/d/Y') && $start_time < date('H:i:s'))
		{
			$this->form_validation->set_message('check_appointment', "You are choose past slot.");
			$check=FALSE;
			return $check;
		}
		return $check;
	 } 
    public function validate_patient_image() {
    $check = TRUE;
   if (!empty($_FILES['d_reg_proof']['name'])) {
    if ($_FILES['d_reg_proof']['size'] == 0) {
        $this->form_validation->set_message('validate_patient_image', 'The Image field is required');
        $check = FALSE;
    }
      else if (isset($_FILES['d_reg_proof']) && $_FILES['d_reg_proof']['size'] != 0)   {
       $allowed =  array('gif','png','jpg','jpeg','GIF','PNG','JPG','JPEG');
       $ext = pathinfo($_FILES['d_reg_proof']['name'],PATHINFO_EXTENSION);
        if(!in_array($ext,$allowed)){
			$this->form_validation->set_message('validate_patient_image', "Invalid file extension of patient image");
            $check = FALSE;
            return $check;
        }
    } 
    }
    return $check;
}
	public function edit_appointment(){
        //if(_is_user_login($this)){
                header('Content-type: text/json');
                $data = array();
                $this->load->library('form_validation');
                $this->form_validation->set_rules('doct_id', 'Doct ID', 'trim|required');
                //$this->form_validation->set_rules('app_id', 'Appointment ID', 'trim|required');
                //$this->form_validation->set_rules('bus_id', 'Business ID', 'trim|required');
                //$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
                //$this->form_validation->set_rules('app_name', 'Full Name', 'trim|required');
                //$this->form_validation->set_rules('email', 'Email', 'trim|required');
                //$this->form_validation->set_rules('phone', 'Phone', 'trim|required');
                $this->form_validation->set_rules('appointment_date', 'App date', 'trim|required');
                $this->form_validation->set_rules('start_time', 'Start Time', 'trim|required');
                $this->form_validation->set_rules('time_token', 'Time Token', 'trim|required');
                /*$this->form_validation->set_rules('services', 'Services', 'trim|required');*/
                $this->form_validation->set_error_delimiters('', '');
                
                if ($this->form_validation->run() == FALSE) 
        		{
                        $data["responce"] = false;   
  			            $data["error"] = $this->form_validation->error_string();
                    
        		}else
                {
                 		$appointment_id=$this->input->post("appointment_id");
                        $doct_id = $this->input->post("doct_id");
                        $bus_id = $this->input->post("bus_id");
                        $user_id = $this->input->post("user_id");
                        $appointment_date = $this->input->post("appointment_date");
                        $start_time = $this->input->post("start_time");
                        $time_token = $this->input->post("time_token");
                        //$patient_img="";
                       $update=array(
                         "bus_id"=>$bus_id,
                         "doct_id"=>$doct_id,
                         "user_id"=>$user_id,
                         "appointment_date"=>date("Y-m-d",strtotime($appointment_date)),
                         "start_time"=>date("H:i:s",strtotime($start_time)),
                         "time_token"=>$time_token,
                         "date_time"=>date("Y-m-d",strtotime($appointment_date)).' '.date("H:i:s",strtotime($start_time))  
                        //"app_name"=>$this->input->post("app_name"),
                        //"app_email"=>$this->input->post("email"),
                        //"app_phone"=>$this->input->post("phone")
                        );
                         //print_r($update);die;
        			 /*if($_FILES["d_reg_proof"]["size"] > 0){
                        $config['upload_path']          = './uploads/patient/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('d_reg_proof'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $update['patient_img'] = $img_data['file_name'];
                        }
                     }*/
                   
                         $this->db->where('id',$appointment_id);
                         $this->db->update("business_appointment",$update);
                         $app_id = $this->db->insert_id();
                         //print($appointment_id);
                         //print($app_id);die;
                        //$business = $this->business_model->get_businesses_by_id($bus_id);
                        
                     /*   $service_array = explode(',',$this->input->post("services"));
                        $this->db->where('busness_appointment_id',$appointment_id);
                        $this->db->delete("business_appointment_services");
                        foreach($service_array as $service){
                        
                            $this->db->insert("business_appointment_services",array("busness_appointment_id"=>$appointment_id,
                            "busness_service_id"=>trim($service),
                            "service_qty"=>1));
                        }*/
                        
                        $data["responce"] = true;
                        $appointment = $this->db->query("Select * from business_appointment where id = '".$app_id."' limit 1");
                        $data["data"] = $appointment->row();
                }
                echo json_encode($data); 
        //}
    }
    public function app_details(){
    	if(_is_user_login($this)){
        $this->load->library('form_validation');
                $this->form_validation->set_rules('appid', 'Appointment ID Required', 'trim|required');
                if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
      		            $appid = $this->input->post("appid");

                        $appointment = $this->business_model->get_business_appointment_by_id($appid);
                        $doctor = $this->business_model->get_businesses_doctor_by_id($appointment->doct_id);
                        $services = $this->business_model->get_business_appointment_service($appid);
                        $user = $this->users_model->get_user_by_id($appointment->user_id);
                        
                        $data["appointment"] = $appointment;
                        $data["services"] = $services;
                        $data["user"] = $user;
                        $data["doctor"] = $doctor;
                       // print_r($data);die;
                        $this->load->view("business/app_details",$data);  
                }
		}
		else
            {
                redirect('admin');
            }
    }
    
    public function list_doctor($bus_id="")
	 {
	 	if(_is_user_login($this)){
            $data = array();
            $data["users"] = $this->doctor_model->get_doctor($bus_id);
            $data["merge_doctors"] = $this->business_model->get_businesses_merge_doctors($bus_id);
          //print_r($data);die;
            $this->load->view("business/doctor/doctor_list",$data);
		}
		else
            {
                redirect('admin');
            }
    }
    public function doctor($bus_id){
        if(_is_user_login($this)){
            $data = array("error"=>"");  

            
                $this->load->library('form_validation');
               
                    $this->form_validation->set_rules('doct_name', 'Doctor Name', 'trim|required');
                    $this->form_validation->set_rules('doct_phone', 'Doctor Phone', 'trim|required');
                    $this->form_validation->set_rules('doct_degree', 'Doctor Degree', 'trim|required');
                
            
              if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
  		            

                          $doct_id =  $this->common_model->data_insert("users",
                                array(
                                "user_fullname"=>$this->input->post("doct_name"),
                                "user_email"=>$this->input->post("doct_email"),
                                "user_password"=>md5($this->input->post("doct_password")),
                                "user_type_id"=>"1",
                                "user_status"=>"1"));     
               if($doct_id){  
        
                $savebus = array(
                            "doct_id"=>$doct_id,
                            "bus_id"=>$bus_id,
                            "doct_name"=>$this->input->post("doct_name"),
                            "doct_degree"=>$this->input->post("doct_degree"),
                            "doct_phone"=>$this->input->post("doct_phone"),
                            "doct_speciality"=>$this->input->post("doct_speciality"),
                            "doct_about"=>$this->input->post('doct_about')
                            );
                
               if($_FILES["doct_logo"]["size"] > 0){
                        $config['upload_path']          = './uploads/business/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('doct_logo'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $savebus["doct_photo"]=$img_data['file_name'];
                        }
                        
                }

                $this->db->insert("business_doctinfo",$savebus);
                    
                    
                    $this->session->set_flashdata("message",'<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Doctor details added Successfully...
                                    </div>');
                    //redirect('admin/business/');
                    //redirect('admin/business/');
               	}
           } 
	       $data["error"] = "";
	       $data["doctors"] = $this->business_model->get_businesses_doctor($bus_id);
            
           $this->load->view('business/doctors',$data);
        }
        else
            {
                redirect('admin');
            }
        
    } 
    public function doctor_delete($doct_id){
        if(_is_user_login($this)){
            $user_id = _get_current_user_id($this);
            $bus_id = $this->db->query('select bus_id from business where user_id ='.$user_id)->row()->bus_id;
            $user_email=$this->db->select('doct_email')->from('business_doctinfo')->where('doct_id',$doct_id)->get()->row()->doct_email;
            //print_r($user_email);die;
            $res= $res=$this->db->select('*')->from('clinic_doctor')->where_in('doct_id',$doct_id)->get()->result();
            if(empty($res))
            {
				$this->db->where('doct_id',$doct_id);
                $this->db->delete("business_doctinfo");
                $this->db->where('doct_id',$doct_id);
                $this->db->delete("business_services");
                $this->session->set_flashdata("message",'<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Doctor deleted Successfully...
                                    </div>');
                redirect("business/list_doctor/".$bus_id);
			}
			else  
			{
				$new_doct_id=array();
				$new_bus_id=array();
			   $bus_id1=$this->db->select('bus_id')->from('business_doctinfo')->where('doct_id',$doct_id)->get()->row()->bus_id;
			   $merge=$this->db->select('*')->from('clinic_doctor')->where('personal_email_id',$user_email)->get()->row();
			   $merge_doct_id=explode(',',$merge->doct_id);
			   $merge_bus_id=explode(',',$merge->bus_id);
			   foreach($merge_doct_id as $doct)
			   {
			   	if($doct != $doct_id)
			   	{
					$new_doct_id = $doct;
				}
			   }
			   foreach($merge_bus_id as $bus)
			   {
			   	if($bus != $bus_id)
			   	{
					$new_bus_id = $bus;
				}
			   }
			    $this->db->where('doct_id',$doct_id);
                $this->db->delete("business_doctinfo");
                $this->db->where('doct_id',$doct_id);
                $this->db->delete("business_services");
			    $this->db->where('id',$merge->id);
			    $this->db->update('clinic_doctor',array('doct_id'=>implode(',',$new_doct_id),'bus_id'=>implode(',',$new_bus_id)));
			   $this->session->set_flashdata("message",'<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Doctor deleted Successfully...
                                    </div>');
                redirect("business/list_doctor/".$bus_id);	
			}
        }
        else
            {
                redirect('admin');
            }
    }
    public function edit_doctor($user_id){
        if(_is_user_login($this)){
            $data = array();
            $data["user_types"] = $this->users_model->get_user_type();
            $user = $this->users_model->get_user_by_id_doctor($user_id);
            $doct_id=$user->doct_id;
            //print_r($user->user_fullname);die;
            $data["doctor"] = $this->users_model->get_doctor($doct_id);
           // $data["user"] = $user;
            //print_r();die;
            if($_POST){
                $this->load->library('form_validation');
                $this->form_validation->set_rules('d_reg_proof','Doctor Registration Proof', 'callback_validate_image');
                $this->form_validation->set_rules('d_qua_proof','Qualification Proof', 'callback_validate_image1');
                $this->form_validation->set_rules('d_id_proof','Id Proof', 'callback_validate_image2');
                $this->form_validation->set_rules('doct_photo','Image', 'callback_validate_image3');
                $this->form_validation->set_rules('user_fullname', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('user_password', 'Password', 'trim|required');
                $this->form_validation->set_rules('d_gender', 'Gender', 'trim|required');
                $this->form_validation->set_rules('doct_degree', 'Doctor Degree', 'trim|required');
                $this->form_validation->set_rules('doct_college', 'Doctor College/ University', 'trim|required');
                $this->form_validation->set_rules('doct_year', 'Year', 'trim|required');
                $this->form_validation->set_rules('doct_speciality', 'Speciality', 'trim|required');
                $this->form_validation->set_rules('doct_experience', 'experience', 'trim|required');
                $this->form_validation->set_rules('city', 'City', 'trim|required');
                //$this->form_validation->set_rules('d_reg_no', 'registration Number', 'trim|required');
               // $this->form_validation->set_rules('d_reg_con', 'registration Council', 'trim|required');
                $this->form_validation->set_rules('d_reg_year', 'registration year', 'trim|required');
                $this->form_validation->set_rules('user_phone', 'Contact Number', 'trim|required|numeric');
                $this->form_validation->set_rules('other_number', 'Other Contact Number', 'trim');
                
                if ($this->form_validation->run() == FALSE) 
        		{
        		   if($this->form_validation->error_string()!=""){
        			$data["error"] = '<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                </div>';
					}
                    
        		}else
                {
                        $user_fullname = $this->input->post("user_fullname");
                        $update_array = array();
                        $user_password = $this->input->post("user_password");
                        if($user->user_password != $user_password && trim($user_password) != ""){
                            $update_array["user_password"]= md5($user_password);
                        }
                                $this->load->library('image_lib');
                                $this->load->library('upload');
                                $config['upload_path'] = './uploads/profile';
                                $config['allowed_types'] ='gif|jpg|png|img|jpeg';
                                $this->upload->initialize($config);
                               // print_r($_FILES['doct_photo']);die;
                               
                       if(isset( $_FILES["doct_photo"]) && $_FILES["doct_photo"]["size"] > 0)
                        {
                        	//print(1);
                            $config['upload_path']          = './uploads/profile/';
                            $config['allowed_types']        = 'gif|jpg|png|jpeg';
                            if(!is_dir($config['upload_path']))
                            {
                                mkdir($config['upload_path']);
                            }
                            $this->load->library('upload', $config);
                            if ( ! $this->upload->do_upload('doct_photo'))
                            {
                                $error = array('error' => $this->upload->display_errors());
                            }
                            else
                            {
                                $img_data = $this->upload->data();
                                $user_image=$img_data['file_name'];
                                $update_array["doct_photo"] =$user_image;
                            }
                        }
                            //print_r($update_array);die;
                            if(!empty($update_array))
                            {
								$this->common_model->data_update("business_doctinfo",$update_array,array("doct_id"=>$user_id));
							}    
                         $update_doct = array(
                                "doct_name"=>$user_fullname,
                                "doct_phone"=>$this->input->post("user_phone"),
                                "d_gender"=>$this->input->post("d_gender"),
                                "doct_degree"=>$this->input->post("doct_degree"),
                                "doct_college"=>$this->input->post("doct_college"),
                                "doct_year"=>$this->input->post("doct_year"),
                                "doct_email"=>$this->input->post("doct_email"),
                                "doct_speciality"=>$this->input->post("doct_speciality"),
                                "doct_experience"=>$this->input->post("doct_experience"),
                                "city"=>$this->input->post("city"),
                                //"d_reg_no"=>$this->input->post("d_reg_no"),
                                //"d_reg_con"=>$this->input->post("d_reg_con"),
                                "awards"=>$this->input->post("awards"),
                                "other_number"=>implode(',',$this->input->post("other_number")),
                                "d_reg_year"=>$this->input->post("d_reg_year")
                        );  
                       // print_r($update_doct);die;     
                           if(isset( $_FILES["d_reg_proof"]) && $_FILES["d_reg_proof"]["size"] > 0)
                        {
                            $config['upload_path']          = './uploads/document/';
                            $config['allowed_types']        = 'gif|jpg|png|jpeg';
                            if(!is_dir($config['upload_path']))
                            {
                                mkdir($config['upload_path']);
                            }
                            $this->load->library('upload', $config);
                            if ( ! $this->upload->do_upload('d_reg_proof'))
                            {
                                $error = array('error' => $this->upload->display_errors());
                            }
                            else
                            {
                                $img_data = $this->upload->data();
                                $user_image=$img_data['file_name'];
                                $update_doct["d_reg_proof"] =$user_image;
                            }
                        }     
 						if(isset( $_FILES["d_qua_proof"]) && $_FILES["d_qua_proof"]["size"] > 0)
                        {
                            $config['upload_path']          = './uploads/document/';
                            $config['allowed_types']        = 'gif|jpg|png|jpeg';
                            if(!is_dir($config['upload_path']))
                            {
                                mkdir($config['upload_path']);
                            }
                            $this->load->library('upload', $config);
                            if ( ! $this->upload->do_upload('d_qua_proof'))
                            {
                                $error = array('error' => $this->upload->display_errors());
                            }
                            else
                            {
                                $img_data = $this->upload->data();
                                $user_image=$img_data['file_name'];
                                 $update_doct["d_qua_proof"] =$user_image;
                            }
                        }     
                         if(isset( $_FILES["d_id_proof"]) && $_FILES["d_id_proof"]["size"] > 0)
                        {
                            $config['upload_path']          = './uploads/document/';
                            $config['allowed_types']        = 'gif|jpg|png|jpeg';
                            if(!is_dir($config['upload_path']))
                            {
                                mkdir($config['upload_path']);
                            }
                            $this->load->library('upload', $config);
                            if ( ! $this->upload->do_upload('d_id_proof'))
                            {
                                $error = array('error' => $this->upload->display_errors());
                            }
                            else
                            {
                                $img_data = $this->upload->data();
                                $user_image=$img_data['file_name'];
                                 $update_doct["d_id_proof"] =$user_image;
                            }
                        }     
                         
                        //print_r($update_doct);die;
						 $this->common_model->data_update("business_doctinfo",$update_doct,array("doct_id"=>$user_id)
                                );

                            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Doctor Update Successfully
                                </div>');
                               
                                redirect("business/list_doctor/".$data["doctor"]->bus_id);    
                }
            }
            
            //print_r($data);die;
            $this->load->view("business/doctor/edit_doctor1",$data);
        }
        else
            {
                redirect('admin');
            }
    }
    public function validate_phone() {
     	 $check="";
     	 $user_id = _get_current_user_id($this);
         $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
        $doct_phone=$this->input->post('user_phone');
        $res=$this->db->select('*')->from('business_doctinfo')->where('doct_phone',$doct_phone)->where('bus_id',$bus_id)->get()->row();
        if(!empty($res))
    	{
    		$this->form_validation->set_message('validate_phone','Contact number already exists with this clinic');
          $check = False;
		}
		else
		{
			 $check = True;
		}
      return $check;
     
   }
 
	public function list_staff($bus_id)
	 {
	 	if(_is_user_login($this)){
            $data = array();
            $data["staffs"] = $this->doctor_model->get_staff($bus_id);
            $data["bus_id"]=$bus_id;
            //print_r($data);die;
            $this->load->view("business/staff/liststaff",$data);
		}
		 else
            {
                redirect('admin');
            }
    }
     public function add_staff($bus_id){
        if(_is_user_login($this)){
            $data = array();
            $data["bus_id"]=$bus_id;
                $this->load->library('form_validation');
                
                $this->form_validation->set_rules('user_fullname', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('user_email', ' User Email Id', 'trim|required|is_unique[staff_info.user_email]');
                $this->form_validation->set_rules('s_email', 'Personal Email Id', 'trim|callback_validate_staff_personal_email_id');
                $this->form_validation->set_rules('user_password', 'Password', 'trim|required');
                $this->form_validation->set_rules('conf_password', 'Confirm Password', 'trim|required|matches[user_password]');
                $this->form_validation->set_rules('user_phone', 'Contact Number', 'trim');
                $this->form_validation->set_rules('user_type', 'User Type', 'trim|required');
                if ($this->form_validation->run() == FALSE) 
        		{
        		  if(!empty($this->form_validation->error_string()))
        		  {
				  	$this->session->set_flashdata("message",'<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                </div>');
				  }
        			
                    
        		}else
                {
                	//print_r('hi');die;
                        $user_fullname = $this->input->post("user_fullname");
                        $user_email = $this->input->post("user_email");
                        $s_email = $this->input->post("s_email");
                        $s_name = $this->input->post("user_fullname");
                        $user_password = $this->input->post("user_password");
                        $user_phone = $this->input->post("user_phone");
                        $user_type = $this->input->post("user_type");
                        
						$res=$this->common_model->data_insert("staff_info",
                                array(
                                "bus_id"=>$bus_id,
                                "user_email"=>$user_email,
                                "user_password"=>md5($user_password),
                                "s_name"=>$s_name,
                                "s_email"=>$s_email,
                                "s_phone"=>$user_phone,
                                "user_type_id"=>$user_type,
                                "created"=>date('Y-m-d H:i:s')));
                                if($res > 0)
                              {
                              	$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Staff Added Successfully
                                </div>');
                                redirect('business/list_staff/'.$bus_id);
							  }  
                            
                        
                }

            $data["user_types"] = $this->users_model->get_user_type();
            $this->load->view("business/staff/add_staff",$data);
        }
        else
        {
                redirect('admin');
        }
    }
     
      public function validate_staff_personal_email_id()
    {
		$check = TRUE;
        $user_id = _get_current_user_id($this);
        $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
    	$s_email=$this->input->post('s_email');
    	$res=$this->db->select('d.*')->from('staff_info d')->where('d.bus_id',$bus_id)->where('d.s_email',$s_email)->get()->row();
    	if(!empty($res))
    	{
    		$this->form_validation->set_message('validate_staff_personal_email_id','This Personal Email exists with this clinic.');
          $check = False;
		}
		else
		{
			 $check = True;
		}
       return $check;
    	
	}
	public function view_doctor($doct_id)
     {
     	if(_is_user_login($this)){
			
	 	$data["doctor"] = $this->business_model->get_doctor_by_id1($doct_id);
            //print_r($data);die;
        $this->load->view('business/doctor/view_doctor1',$data);
		}
		else
        {
                redirect('admin');
        }
	 }
    public function view_staff($bus_id,$user_id){
        if(_is_user_login($this)){
            $data = array();
            $data["user_types"] = $this->users_model->get_user_type();
            //$user = $this->users_model->get_user_by_id($user_id);
            //$data["user"] = $user;
            $data['user'] = $this->users_model->get_staff_by_id($user_id);
            $data['bus_id']=$bus_id;
            //print_r($data);die;
            $this->load->view("business/staff/view_staff",$data);
           }
        else
        {
                redirect('admin');
        }
        }
        
   public function edit_staff($bus_id,$user_id){
   	if(_is_user_login($this)){
            $data = array();
            $update_doct=array();
            $data["user_types"] = $this->users_model->get_user_type();
            $user=$user=$this->db->select('*')->from('staff_info')->where('user_email',$this->input->post('user_email'))->get()->row();
            $data['staff'] = $this->users_model->get_staff_by_id($user_id);
            $data['staff_id']=$user_id;
            $data['bus_id']=$bus_id;
            if($_POST){
                $this->load->library('form_validation');
                $this->form_validation->set_rules('user_fullname', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('user_email', 'User Email', 'trim|required');
                $this->form_validation->set_rules('user_password', 'Password', 'trim|required');
                $this->form_validation->set_rules('conf_password', 'Confirm Password', 'trim|required|matches[user_password]');
                $this->form_validation->set_rules('user_phone', 'User Phone', 'trim');
                $this->form_validation->set_rules('s_email', 'Personal Email', 'trim');
                $this->form_validation->set_rules('user_type', 'User Type', 'trim|required');
                if ($this->form_validation->run() == FALSE) 
        		{
        			 if($this->form_validation->error_string()!=""){
        			 $this->session->set_flashdata('message','<div class="alert alert-warning alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                </div>');
					}
                    
        		}else
                {
                        $user_fullname = $this->input->post("user_fullname");
                        $user_phone = $this->input->post("user_phone");
                        $s_email = $this->input->post("s_email");
                        $user_type = $this->input->post("user_type");
                        
                        $user_password = $this->input->post("user_password");
                        $update_doct = array(
                                "s_name"=>$user_fullname,
                                "s_phone"=>$user_phone,
                                "user_type_id"=>$user_type,
                                "s_email"=>$s_email );   
                        if($user->user_password != $user_password && trim($user_password) != ""){
                            $update_doct["user_password"]= md5($user_password);
                        }
						 $this->common_model->data_update("staff_info",$update_doct,array("staff_id"=>$user_id));

                            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Record Updated Successfully
                                </div>');
                               
                                redirect("business/list_staff/".$bus_id);
                        
                }
            }
            $this->load->view("business/staff/edit_staff",$data);
        }
    else
        {
                redirect('admin');
        }    
        }
        
   function delete_staff($user_id){
        if(_is_user_login($this)){
            $data = array();
            $user  = $this->users_model->get_user_staff_by_id($user_id);
            if($user){
            	
                $this->db->query("Delete from staff_info where staff_id = '".$user->staff_id."'");
                $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong>Delete Successfully..
                                </div>');
                redirect("business/list_staff/".$user->bus_id);
            }
        }
        else
        {
                redirect('admin');
        }
    }

       
	public function add_doctor(){
		if(_is_user_login($this)){
		       $bus_id="";
		       $bus=$this->business_model->get_businesses(3);
		       foreach($bus as $b)
		       {
			   	$bus_id=$b->bus_id;
			   }
                $data = array("error"=>"");  	
                $this->load->library('form_validation');
                $this->form_validation->set_rules('user_fullname', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('d_reg_proof','Doctor Registration Proof', 'callback_validate_image');
                $this->form_validation->set_rules('d_qua_proof','Qualification Proof', 'callback_validate_image1');
                $this->form_validation->set_rules('d_id_proof','Id Proof', 'callback_validate_image2');
                $this->form_validation->set_rules('doct_photo','Image', 'callback_validate_image3');
                $this->form_validation->set_rules('user_email','User Email Id', 'trim|required|is_unique[business_doctinfo.user_email]');
                $this->form_validation->set_rules('doct_email','Personal Email Id', 'trim|required|callback_validate_personal_email_id');
                $this->form_validation->set_rules('user_password', 'Password', 'trim|required');
                $this->form_validation->set_rules('conf_password', 'Confirm Password', 'trim|required|matches[user_password]');
                $this->form_validation->set_rules('city', 'City Name', 'trim|required');
                $this->form_validation->set_rules('gender', 'Gender', 'trim|required');
                $this->form_validation->set_rules('doct_speciality', 'Specilization', 'trim|required');
                 $this->form_validation->set_rules('speciality_cat', 'Category', 'trim|required');
                $this->form_validation->set_rules('doct_experience', 'Experience', 'trim|required');
                $this->form_validation->set_rules('doct_degree', 'Degree', 'trim|required');
                $this->form_validation->set_rules('user_phone', 'Contact Number', 'trim|required');
                $this->form_validation->set_rules('doct_college', 'College/ University', 'trim|required');
                $this->form_validation->set_rules('doct_year', 'Pass Out Year', 'trim|required');
                $this->form_validation->set_rules('consult_fee', 'Consult fee', 'trim|required');
                $this->form_validation->set_rules('d_reg_no', 'Registration No', 'trim|required|callback_check_registration');
                $this->form_validation->set_rules('d_reg_con', 'Registration Council', 'trim|required|callback_check_council');
                $this->form_validation->set_rules('d_reg_year', 'Registration Year', 'trim|required');
              if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
 					 $doct_photo="";
        			 if($_FILES["doct_photo"]["size"] > 0){
                        $config['upload_path']          = './uploads/profile/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('doct_photo'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $doct_photo=$img_data['file_name'];
                        }
                        
                        }
                        $consult_fee=$this->input->post("consult_fee");
                        $consult=explode('-',$consult_fee);
                $savebus = array(
                            "user_email"=>$this->input->post("user_email"),
                            "user_password"=>md5($this->input->post("user_password")),
                            "bus_id"=>$bus_id,
                            "doct_name"=>$this->input->post("user_fullname"),
                            "doct_email"=>$this->input->post("doct_email"),
                            "d_gender"=>$this->input->post("gender"),
                            "doct_phone"=>$this->input->post("user_phone"),
                            "main_category"=>$this->input->post("speciality_cat"),
                            "doct_speciality"=>$this->input->post("doct_speciality"),
                            "doct_photo"=>$doct_photo,
                            "doct_experience"=>$this->input->post("doct_experience"),
                            "doct_degree"=>$this->input->post("doct_degree"),
                            "doct_college"=>$this->input->post("doct_college"),
                            "doct_year"=>$this->input->post("doct_year"),
                            "d_reg_no"=>$this->input->post("d_reg_no"),
                            "d_reg_con"=>$this->input->post("d_reg_con"),
                            "d_reg_year"=>$this->input->post("d_reg_year"),
                            "city"=>$this->input->post("city"),
                            "awards"=>$this->input->post("awards"),
                            "consult_fee"=>$consult_fee,
                            "consult_to"=>$consult['0'],
                            "consult_from"=>$consult['1'],
                            "other_number"=>implode(',',$this->input->post("other_number"))
                            );
                
               if($_FILES["d_reg_proof"]["size"] > 0){
                        $config['upload_path']          = './uploads/document/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('d_reg_proof'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $savebus["d_reg_proof"]=$img_data['file_name'];
                        }   
                }
			   if($_FILES["d_qua_proof"]["size"] > 0){
                        $config['upload_path']          = './uploads/document/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('d_qua_proof'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $savebus["d_qua_proof"]=$img_data['file_name'];
                        }   
                }
			   if($_FILES["d_id_proof"]["size"] > 0){
                        $config['upload_path']          = './uploads/document/';
                        $config['allowed_types']        = 'gif|jpg|png|jpeg';
                        $this->load->library('upload', $config);
        
                        if ( ! $this->upload->do_upload('d_id_proof'))
                        {
                                $error = array('error' => $this->upload->display_errors());
                        }
                        else
                        {
                            $img_data = $this->upload->data();
                            $savebus["d_id_proof"]=$img_data['file_name'];
                        } 
                }		
				
                    $id=$this->db->insert("business_doctinfo",$savebus);
                    if($id > 0)
                    {
						$this->session->set_flashdata("message",'<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Doctor details added Successfully...
                                    </div>');
                    redirect('business/list_doctor/'.$bus_id);
					}
                    else
                    {
					    $this->session->set_flashdata("message",'<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-check"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> Fail to add details...
                                    </div>');
                    redirect('business/list_doctor/'.$bus_id);	
					}
                    //redirect('admin/business/');
              
           } 
	       $data["error"] = "";
	       /*$data["doctors"] = $this->business_model->get_businesses_doctor($bus_id);*/
            $data1['bus_id']=$bus_id;
           $this->load->view('business/doctor/add_doctor',$data1);
		}
        else
        {
                redirect('admin');
        } 
     }

    public function validate_personal_email_id()
    {
		$check = TRUE;
        $user_id = _get_current_user_id($this);
        $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
    	$doct_email=$this->input->post('doct_email');
    	$res=$this->db->select('d.*')->from('business_doctinfo d')->where('d.bus_id',$bus_id)->where('d.doct_email',$doct_email)->get()->row();
    	if(!empty($res))
    	{
    		$this->form_validation->set_message('validate_personal_email_id','This Personal Email exists with this clinic.');
          $check = False;
		}
		else
		{
			 $check = True;
		}
       return $check;
    	
	}
    public function check_registration() {
        $check = TRUE;
        $user_id = _get_current_user_id($this);
        $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
         //print_r($bus_id);
         //print_r($this->input->post('d_reg_no'));
    	$reg_no=$this->input->post('d_reg_no');
    	$doct_email=$this->input->post('doct_email');
    	$res=$this->db->select('d.*')->from('business_doctinfo d')->join('business b','b.bus_id=d.bus_id')->where('d.bus_id',$bus_id)->where('d.d_reg_no',$reg_no)->get()->row();
    	//print_r($res);die;
    	if(!empty($res))
    	{
    		$this->form_validation->set_message('check_registration','Registration number already exists with this clinic');
          $check = False;
		}
		else
		{
			 $check = True;
		}
      return $check;
}
    public function check_council() {
        $check = TRUE;
        $user_id = _get_current_user_id($this);
        $bus_id=$this->db->select('bus_id')->from('business')->where('user_id',$user_id)->get()->row()->bus_id;
    	$d_reg_con=$this->input->post('d_reg_con');
    	$doct_email=$this->input->post('doct_email');
    	$res=$this->db->select('d.*')->from('business_doctinfo d')->join('business b','b.bus_id=d.bus_id')->where('d.bus_id',$bus_id)->where('d.d_reg_con',$d_reg_con)->get()->row();
    	if(!empty($res))
    	{
			$this->form_validation->set_message('check_council','Council number already exists with this clinic');
          $check = False;
		}
		else
		{
			 $check = True;
		}
      return $check;
}
    public function validate_image() {
    $check = TRUE;
    if ((count($_FILES['d_reg_proof']['size'])) == 0) {
        $this->form_validation->set_message('validate_image', 'The Image field is required');
        $check = FALSE;
    }
      else if (isset($_FILES['d_reg_proof']) && $_FILES['d_reg_proof']['size'] != 0)   {
       $allowed =  array('gif','png','jpg','jpeg','GIF','PNG','JPG','JPEG');
       $ext = pathinfo($_FILES['d_reg_proof']['name'],PATHINFO_EXTENSION);
        if(!in_array($ext,$allowed)){
			$this->form_validation->set_message('validate_image', "Invalid file extension of registration proof");
            $check = FALSE;
            return $check;
        }
    }
    return $check;
}
 
    public function validate_image1() {
    $check = TRUE;
    if ((count($_FILES['d_qua_proof']['size'])) == 0) {
        $this->form_validation->set_message('validate_image1', 'The Image field is required');
        $check = FALSE;
    }
      else if (isset($_FILES['d_qua_proof']) && $_FILES['d_qua_proof']['size'] != 0)   {
       $allowed =  array('gif','png','jpg','jpeg','GIF','PNG','JPG','JPEG');
       $ext = pathinfo($_FILES['d_qua_proof']['name'],PATHINFO_EXTENSION);
       //print_r($ext);die;
        if(!in_array($ext,$allowed)){
			$this->form_validation->set_message('validate_image1', "Invalid file extension qualification proof");
            $check = FALSE;
            return $check;
        }
    }
    return $check;
}

    public function validate_image2() {
    $check = TRUE;
    if ((count($_FILES['d_id_proof']['size'])) == 0) {
        $this->form_validation->set_message('validate_image2', 'The Image field is required');
        $check = FALSE;
    }
      else if (isset($_FILES['d_id_proof']) && $_FILES['d_id_proof']['size'] != 0)   {
       $allowed =  array('gif','png','jpg','jpeg','GIF','PNG','JPG','JPEG');
       $ext = pathinfo($_FILES['d_id_proof']['name'],PATHINFO_EXTENSION);
        if(!in_array($ext,$allowed)){
			$this->form_validation->set_message('validate_image2', "Invalid file extension id proof");
            $check = FALSE;
            return $check;
        }
    }
    return $check;
}

    public function validate_image3() {
    $check = TRUE;
    if ((count($_FILES['doct_photo']['size'])) == 0) {
        $this->form_validation->set_message('validate_image3', 'The Image field is required');
        $check = FALSE;
    }
      else if (isset($_FILES['doct_photo']) && $_FILES['doct_photo']['size'] != 0)   {
       $allowed =  array('gif','png','jpg','jpeg','GIF','PNG','JPG','JPEG');
       $ext = pathinfo($_FILES['doct_photo']['name'],PATHINFO_EXTENSION);
        if(!in_array($ext,$allowed)){
			$this->form_validation->set_message('validate_image3', "Invalid file extension doctor image");
            $check = FALSE;
            return $check;
        }
    }
    return $check;
}

    public function patient_details()
    {
    	if(_is_user_login($this)){
		$this->load->view('business/patient/patient_details');
		}
		else
        {
                redirect('admin');
        } 
	}
	 public function clinic_report($bus_id="",$doct_id="")
     {
	 	 if(_is_user_login($this))
       {
       		$user_id = _get_current_user_id($this);
            $data = array();
            $from_date = "";
            $to_date = "";
               
                if($this->input->post("date_range")){
                    $date_range =  $this->input->post("date_range");
                    $date_range = explode(',',$date_range);
                    $from_date = trim($date_range[0]);
                    $to_date = trim($date_range[1]);    
                }
                if($doct_id == ""){
                    $doct_id = $this->input->post("filter_doct");
                    $data["doctors"] = $this->business_model->get_businesses_doctor($bus_id);
                }
                   $data['date_range_lable'] = $this->input->post('date_range_lable');
                  $data["appointment"]  = $this->doctor_model->get_doctor_appointment_by_id_for_clinic($bus_id,'',$from_date,$to_date,$doct_id);
                  //print_r($data["appointment"]);die;
                  $data["app_revenue"]  = $this->business_model->get_business_app_revenue2($from_date,$to_date,$bus_id,$doct_id);
                  $data["walk_revenue"]  = $this->business_model->get_business_walk_in_revenue1($from_date,$to_date,$bus_id,$doct_id);
                  $data["telephonic_revenue"]  = $this->business_model->get_business_telephonic_revenue1($from_date,$to_date,$bus_id,$doct_id);
                 // print_r($data["app_revenue"]);die;
                 $data["doctors"] = $this->business_model->get_businesses_doctor($bus_id); 
                 //print_r($data);die; 
                 $this->load->view('business/report/report_view',$data);
		}
		else
		{
			redirect('admin');
		}
	 }
	 public function business_delete($id){
        if(_is_user_login($this)){
	       
            $data = array();
            $business  = $this->business_model->get_businesses_by_doct($id);
            $res = $this->db->query("select * from business where bus_id ='".$id."'");
            $user_id=$res->row()->user_id;
            //print_r($user_id);die;
           if($user_id != ""){
                $this->db->query("Delete from business where bus_id = '".$user_id."'");
                $this->db->query("Delete from business_doctinfo where bus_id = '".$user_id."'");
				$this->db->query("Delete from staff_info where bus_id = '".$user_id."'");  
                $this->db->query("Delete from business_category where bus_id = '".$user_id."'");
                //$this->db->query("Delete from business_appointment where bus_id = '".$user_id."'");
            $this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                        <i class="fa fa-Success"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Success!</strong> Clinic Deleted Successfully...
                                    </div>');    
			                        $this->session->sess_destroy();
			                        redirect("admin");
           }
        }
        else
        {
        	
             $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> Fail to delete Account
                                    </div>');
                                    redirect('admin');
        }
    }
    public function billing($bus_id)
	{
		 if(_is_user_login($this))
       {
       	  $this->load->library('form_validation');
	      $this->form_validation->set_rules('app_id', 'patient Name', 'trim|required');
	      $this->form_validation->set_rules('total_amount', 'Total Amount', 'trim|required|callback_check_total_amount|numeric');
         
              if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
        			$app_id=$this->input->post('app_id');
        			$user=$this->db->query('select * from business_appointment where id ='.$app_id)->row();
                                	//print_r($user);
									$appointments = array(
				                                "payment_amount"=>$this->input->post("total_amount"),
				                                "payment_mode"=>"cash",
				                                );
				                                //"payed_amount"=>$this->input->post("payed_amount"),
				                                //"due_amount"=>$this->input->post("remaining_amount") );
                              
                                  $this->common_model->data_update("business_appointment",$appointments,array('id'=>$app_id));
                            $billing = array(
				                                "user_id"=>$user->user_id,
				                                "sub_user_id"=>$user->sub_user_id,
				                                "app_id"=>$user->id,
				                                "doct_id"=>$user->doct_id,
				                                "bus_id"=>$user->bus_id,
				                                "service"=>implode(',',$this->input->post("service")),
				                                "amount"=>implode(',',$this->input->post("amount")),
				                                "discount"=>$this->input->post("discount"),
				                                "total_amount"=>$this->input->post("total_amount"),
				                                /*"payed_amount"=>$this->input->post("payed_amount"),
				                                "remaining_amount"=>$this->input->post("remaining_amount")*/
                               );
                              
                            $id=$this->common_model->data_insert("billing",$billing);
                            if($id > 0)
                            {
								$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Payment done successfully.
                                </div>');
                                redirect("business/billing/".$bus_id);
							}
                           else
                           {
						   	 $this->session->set_flashdata("message", '<div class="alert alert-danger alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> Fail To Payment. 
                                </div>');
                                redirect("business/billing/".$bus_id);
						   } 
			}
       		
       		$data['services']=$this->doctor_model->get_bus_services($bus_id);
            $data['searches'] = $this->doctor_model->get_patient_for_clinic($bus_id);
            $data['bus_id']=$bus_id;
           //print_r($data);die;
            $this->load->view('business/patient/patient_billing',$data);
		
	   }
       else
        {
                redirect('admin');
        }       
	}
	
	public function check_total_amount()
	{
		$check=TRUE;
		$total_amount=$this->input->post('total_amount');
		if($total_amount == 0)
		{
			$this->form_validation->set_message('check_total_amount','total amount not accepted 0');
			$check=FALSE;
		}
		return $check;
	}
	
	public function revenue_bar_graph($bus_id) 
    {
    	if(_is_user_login($this)){
        	$data["today"]=$this->business_model->get_business_today_revenue($bus_id);
            $data["week"]=$this->business_model->get_business_week_revenue($bus_id);
            $data["month"]=$this->business_model->get_business_month_revenue($bus_id);
			$this->load->view('business/report/bar_graph_view',$data);
		}
		else
        {
                redirect('admin');
        } 	
		}
    
    public function search_patient()
    {
		//print_r($this->input->post('patient_id'));
		$app_id=$this->input->post('patient_id');
		$data =$this->db->select('a.*,u.user_fullname,u.user_phone,u.user_email,u.user_image,u.user_bdate,u.salutations,u.gender')->from('business_appointment a')->join('users u','u.user_id=a.user_id','left')->where('a.id',$app_id)->get()->row();
		echo json_encode($data); 
	}
	
	 public function perticular_patient_billing($id)
	{
		 if(_is_user_login($this))
       {
       	   $service=$this->input->post('service[]');
       	  // print_r($service);die;
       	   $bus_id = _get_current_user_id($this);
       	   //print_r($bus_id);die;
       	   $this->load->library('form_validation');
	       $this->form_validation->set_rules('app_id', 'patient Name', 'trim|required');
	       $this->form_validation->set_rules('total_amount', 'Total Amount', 'trim|required|numeric|callback_check_zero');
              if ($this->form_validation->run() == FALSE)
        		{
  		            if($this->form_validation->error_string()!=""){
        			     $this->session->set_flashdata("message", '<div class="alert alert-warning alert-dismissible" role="alert">
                                        <i class="fa fa-warning"></i>
                                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                      <strong>Warning!</strong> '.$this->form_validation->error_string().'
                                    </div>');
                    }
        		}
        		else
        		{
        			
        			$user=$this->db->query('select * from business_appointment where id ='.$id)->row();
                                	//print_r($user);
									$appointments = array(
				                                "payment_amount"=>$this->input->post("total_amount"),
				                                "payment_mode"=>"cash",
				                                //"payed_amount"=>$this->input->post("payed_amount"),
				                                //"due_amount"=>$this->input->post("remaining_amount") 
				                                );
                              
                                  $this->common_model->data_update("business_appointment",$appointments,array('id'=>$id));
                            $billing = array(
				                                "user_id"=>$user->user_id,
				                                "sub_user_id"=>$user->sub_user_id,
				                                "app_id"=>$user->id,
				                                "doct_id"=>$user->doct_id,
				                                "bus_id"=>$user->bus_id,
				                                "service"=>implode(',',$this->input->post("service")),
				                                "amount"=>implode(',',$this->input->post("amount")),
				                                "discount"=>$this->input->post("discount"),
				                                "total_amount"=>$this->input->post("total_amount"),
				                                /*"payed_amount"=>$this->input->post("payed_amount"),
				                                "remaining_amount"=>$this->input->post("remaining_amount")*/
                               );
                              
                            $id1=$this->common_model->data_insert("billing",$billing);
                            if($id1 > 0)
                            {
								$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success!</strong> Payment done successfully.
                                </div>');
                                redirect("business/patient_list/".$id);
							}
                           else
                           {
						   	 $this->session->set_flashdata("message", '<div class="alert alert-danger alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> Fail To Payment. 
                                </div>');
                                redirect("business/patient_list/".$id);
						   } 
			}
       		
       		$data['services']=$this->doctor_model->get_bus_services($bus_id);
            $data['searches'] = $this->db->select('a.*,u.user_fullname as app_name,u.user_phone as app_phone,u.user_email as app_email')->from('business_appointment a')->join('users u','u.user_id=a.user_id','left')->where('a.id',$id)->get()->result_array();
            $data['id']=$id;
            $this->load->view('doctor/patient/patient_billing_view',$data);
		
	   }  
        else
        {
                redirect('admin');
        }    
	}
	 public function check_zero()
    {
		$check = TRUE;
    if ($this->input->post('total_amount') == 0) {
        $this->form_validation->set_message('check_zero', 'Total Amount Not Accept Zero');
        $check = FALSE;
     }
     return $check;
	}
	
	public function delete_doctor($doct_id)
	{
		    $data= array('delete_status'=>1);
			$this->db->where('doct_id',$doct_id);
	 		$id=$this->db->update('business_doctinfo',$data);
	 		$bus_id = _get_current_user_id($this);
	 		$clinic=$this->db->select('*')->from('business')->where('bus_id',$bus_id)->get()->row();
	 		$request = $this->db->query("Select * from business_doctinfo where doct_id = '".$doct_id."' limit 1");
					 if($request->num_rows() > 0){
                                $user = $request->row();
                                $this->load->library('email');
           						$this->email->from('contact@explicate.in');
           						$this->email->to($user->user_email);
            					$this->email->subject("Account Mail");
                                $message = " Hi, ".$user->doct_name." \n Your Account deleted by ".$clinic->bus_title.". If any issue please contact to clinic. \n Thank You! \n Ziffytech Team.";
                                $this->email->message($message);

                                if ( ! $this->email->send()){
                                	 $this->session->set_flashdata("message", '<div class="alert alert-danger alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> Something is wrong with system to send mail. 
                                </div>');
                                }else{
                                	$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success</strong> Successfully Send Mail 
                                </div>');
                                    }
                             }
				    else
				    {
					     $this->session->set_flashdata("message", '<div class="alert alert-danger alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> No user found with this email 
                                </div>');
                    }
                    $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>$doct_id,
                        'notification'=>$clinic->bus_title." has delete your account at ".date('Y-m-d H:i:s').".",
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s'));
                        $n_id=$this->db->insert('doctor_notification',$notification);
                    redirect('business/list_doctor/'.$bus_id);
		
	}
    
    public function active_doctor($doct_id)
	{
		if(_is_user_login($this)){
    		$data= array('delete_status'=>0);
			$this->db->where('doct_id',$doct_id);
	 		$id=$this->db->update('business_doctinfo',$data);
	 		$bus_id = _get_current_user_id($this);
	 		$clinic=$this->db->select('*')->from('business')->where('bus_id',$bus_id)->get()->row();
	 		
	 		$request = $this->db->query("Select * from business_doctinfo where doct_id = '".$doct_id."' limit 1");
					 if($request->num_rows() > 0){
                                $user = $request->row();
                                $this->load->library('email');
           						$this->email->from('contact@explicate.in');
           						$this->email->to($user->user_email);
            					$this->email->subject("Account Mail");
                                $message = " Hi, ".$user->doct_name."\n Your Account activated by ".$clinic->bus_title.". If any issue please contact to clinic. \n Thank You! \n Ziffytech Team";
                                $this->email->message($message);
                                if ( ! $this->email->send()){
                                	$this->session->set_flashdata("message", '<div class="alert alert-danger alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> Something is wrong with system to send mail. 
                                </div>');
                                }else{
                                	$this->session->set_flashdata("message", '<div class="alert alert-success alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Success</strong> Successfully Send Mail 
                                </div>');
                                    }
                             }
				    else
				    {
					     $this->session->set_flashdata("message", '<div class="alert alert-danger alert-dismissible" role="alert">
                                  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                  <strong>Warning!</strong> No user found with this email 
                                </div>');
                    }
                    $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>$doct_id,
                        'notification'=>$clinic->bus_title." has active your account at ".date('Y-m-d H:i:s').".",
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s'));
                        $n_id=$this->db->insert('doctor_notification',$notification);
                    redirect('business/list_doctor/'.$bus_id);
		}
	}
	
	 public function change_delete_status(){
        
        $table = $this->input->post("table");
        $id = $this->input->post("id");
        $on_off = $this->input->post("on_off");
        $id_field = $this->input->post("id_field");
        $status = $this->input->post("status");
        $this->db->update($table,array("$status"=>$on_off),array("$id_field"=>$id));
        $bus_id = _get_current_user_id($this);
	 	$clinic=$this->db->select('*')->from('business')->where('bus_id',$bus_id)->get()->row();
       // echo $on_off;
         if($table =='business_doctinfo' && $on_off == '1')
         {
					$request = $this->db->query("Select * from business_doctinfo where doct_id = '".$id."' limit 1");
					 if($request->num_rows() > 0){
                                $user = $request->row();
                                $this->load->library('email');
           						$this->email->from('contact@explicate.in');
           						$this->email->to($user->user_email);
            					$this->email->subject("Activation Mail");
                                $message = " Hi, ".$user->doct_name."\n \n Your Account has been activated by the ".$clinic->bus_title.". You can now login into your account. \n \n Thank You! \n Team Ziffytech";
                                $this->email->message($message);

                                //print_r($this->email->send());die;
                                if ( ! $this->email->send()){
                                	echo "Something is wrong with system to send mail.";
                              
                                }else{
                                	echo "Successfully Send Mail";
                              
                                    }
                   }
				else{
					echo "No user found with this email";
                      
                   }
                   $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>$id,
                        'notification'=>$clinic->bus_title." has active your account at ".date('Y-m-d H:i:s').".",
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s'));
                       $n_id=$this->db->insert('doctor_notification',$notification);                 
				}
         elseif($table =='business_doctinfo' && $on_off == '0')
         {
					$request = $this->db->query("Select * from business_doctinfo where doct_id = '".$id."' limit 1");
					 if($request->num_rows() > 0){
                                $user = $request->row();
                                $this->load->library('email');
           						$this->email->from('contact@explicate.in');
           						$this->email->to($user->user_email);
            					$this->email->subject("Activation Mail");
                                $message = " Hi, ".$user->doct_name." \n \n Your Account has been deleted by the ".$clinic->bus_title.". If any query, Please contact to clinic. \n \n Thank You! \n Team Ziffytech";
                                $this->email->message($message);

                                //print_r($this->email->send());die;
                                if ( ! $this->email->send()){
                                	echo "Something is wrong with system to send mail.";
                              
                                }else{
                                	echo "Successfully Send Mail";
                                 }
                $notification=array(
                        'sender_id'=>_get_current_user_id($this),
                        'receiver_id'=>$id,
                        'notification'=>$clinic->bus_title ." has delete your account at ".date('Y-m-d H:i:s').".",
                        'n_type'=>'1',
                        'created'=>date('Y-m-d-H:i:s'));
                       $n_id=$this->db->insert('doctor_notification',$notification);                    
                   }
				else{
					echo "No user found with this email";
                      
                   }
				}
    }

}
