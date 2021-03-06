<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Uploads extends CI_Controller {

    private $upload_path;

    public function __construct() {
        parent::__construct();
        Auth::validate_request();
        $this->load->database();
        $this->load->library(array('form_validation', 'my_upload'));
        $this->load->helper('array');
        $this->upload_path = dirname(APPPATH) . '/uploads/';
        header('Content-Type:application/json');
    }

    function index() {
        echo 1;
    }

    /**
     * remove a file with its replications
     */
    function remove() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->load->helper('file');
            $file = json_decode($this->input->post('file'));
           
            $result = array();
            foreach ($file as $replication) {
                $result[] = unlink(dirname(APPPATH). $replication->full_path);
            }
            
        }else{
            echo 'NOT PERMITTED';
        }
    }

    function upload($fieldId = FALSE) {

//        echo json_encode(array($_FILES, count($_FILES)));
//        die;

        $field = $this->db->get_where('fields', array('id' => $fieldId), 1)->row();
        $attrs = json_decode($field->attrs);
        $data['errors'] = array();
        $data['upload_data'] = array();

        // default values
        $config['path'] = $this->upload_path;
        $config['upload_path'] = $config['path'];
        $config['allowed_types'] = 'gif|jpg|png';
        $config['prefix'] = '';
        $config['suffix'] = '';
        $config['max_size'] = 2500 * 1024;
        $config['max_width'] = 3000;
        $config['max_height'] = 2000;

        $config['thumb_upload_path'] = $config['path'] . 'thumbs/';
        $config['thumb_prefix'] = '';
        $config['thumb_suffix'] = '';
        $config['thumb_width'] = 300;
        $config['thumb_height'] = 200;
        $config['thumb_ratio'] = true;


        if (isset($attrs)) {

            if (isset($attrs->path) && strlen($attrs->path) > 0) {
                $attrs->path = trim($attrs->path, '/');
                $config['upload_path'] = $config['path'] . $attrs->path;
            }

            if (isset($attrs->max_size)) {
                $config['max_size'] = $attrs->max_size * 1024;
            }

            if (isset($attrs->max_width)) {
                $config['max_width'] = $attrs->max_width;
            }

            if (isset($attrs->max_height)) {
                $config['max_height'] = $attrs->max_height;
            }
            if (isset($attrs->allowed_types)) {
                $config['allowed_types'] = $attrs->allowed_types;
            }

            if (isset($attrs->prefix)) {
                $config['prefix'] = $attrs->prefix;
            }

            if (isset($attrs->suffix)) {
                $config['suffix'] = $attrs->suffix;
            }


// thumbnail 

            if (isset($attrs->thumb_path) && strlen($attrs->thumb_path) > 0) {
                $attrs->thumb_path = trim($attrs->thumb_path, '/');
                $config['thumb_upload_path'] = $config['path'] . $attrs->thumb_path;
            }

            if (isset($attrs->thumb_prefix)) {
                $config['thumb_prefix'] = $attrs->thumb_prefix;
            }

            if (isset($attrs->thumb_suffix)) {
                $config['thumb_suffix'] = $attrs->thumb_suffix;
            }

            if (isset($attrs->thumb_width)) {
                $config['thumb_width'] = $attrs->thumb_width;
            }

            if (isset($attrs->thumb_height)) {
                $config['thumb_height'] = $attrs->thumb_height;
            }

            if (isset($attrs->thumb_ratio)) {
                $config['thumb_ratio'] = $attrs->thumb_ratio;
            }
        }

//        print_r($config);die;
        //$this->load->library('upload', $config);

        if (isset($attrs->max) && count($_FILES) > $attrs->max) {
            $data['errors'][] = "The maximum number of files is: $attrs->max";
            echo json_encode($data);
            die;
        }else if(empty($_FILES)){
            $data['errors'][] = "No file selected";
            echo json_encode($data);
            die;
        }
        

        foreach ($_FILES as $value) {

            $this->my_upload->upload($value);
            if ($this->my_upload->uploaded == true) {

                $this->my_upload->allowed = array('image/*');

                $this->my_upload->file_name_body_add = $config['suffix'];
                $this->my_upload->file_name_body_pre = $config['prefix'];
//                $this->my_upload->file_max_size = $config['max_size'];
//                $this->my_upload->image_max_width = $config['max_width'];
//                $this->my_upload->image_max_height = $config['max_height'];

                if ($this->my_upload->file_src_size > $config['max_size']) {
                    $data['errors'][] = 'Maximum size exceeded, the maximum is : ' . ceil($config['max_size'] / 1024) . 'Kb';
                } else
                if ($this->my_upload->image_src_x > $config['max_width']) {
                    $data['errors'][] = 'Maximum width exceeded, the maximum is : ' . $config['max_width'];
                } else
                if ($this->my_upload->image_src_y > $config['max_height']) {
                    $data['errors'][] = 'Maximum height exceeded, the maximum is : ' . $config['max_height'];
                } else
                if (!in_array($this->my_upload->file_src_name_ext, explode('|', $config['allowed_types']))) {
                    $data['errors'][] = 'Extension unsuported, valid extensions are :' . $config['allowed_types'];
                } else {

                    $result = array();

                    $this->my_upload->process($config['upload_path']);
                    if ($this->my_upload->processed == true) {
                        $result[] = array(
                            'name' => $this->my_upload->file_dst_name,
                            'full_path' => ltrim(str_replace('\\', '/', $this->my_upload->file_dst_pathname), '.'),
                            'ext' => $this->my_upload->file_dst_name_ext,
                            'mime' => $this->my_upload->file_src_mime,
                            'size' => $this->my_upload->file_src_size,
                        );
                    }


                    if (isset($attrs->thumb) && $attrs->thumb) {
                        // create thumbnail
                        $this->my_upload->file_name_body_add = $config['thumb_suffix'];
                        $this->my_upload->file_name_body_pre = $config['thumb_prefix'];
                        $this->my_upload->image_resize = true;
                        $this->my_upload->image_x = $config['thumb_width'];
                        $this->my_upload->image_y = $config['thumb_height'];
                        $this->my_upload->image_ratio = $config['thumb_ratio'];
                        $this->my_upload->process($config['thumb_upload_path']);

                        if ($this->my_upload->processed == true) {
                            $result[] = array(
                                'name' => $this->my_upload->file_dst_name,
                                'full_path' => ltrim(str_replace('\\', '/', $this->my_upload->file_dst_pathname), '.'),
                                'ext' => $this->my_upload->file_dst_name_ext,
                                'mime' => $this->my_upload->file_src_mime,
                                'size' => $this->my_upload->file_src_size,
                            );
                        } else {
                            $data['errors'][] = $this->my_upload->error;
                        }
                    }
                    $this->my_upload->clean();
                    $data['upload_data'][] = $result;
                }
            } else {
                $data['errors'][] = $this->my_upload->error;
            }
        }

        echo json_encode($data);
        die;

//        if (!$this->upload->do_upload($fieldName)) {
//            $error = array('error' => $this->upload->display_errors());
//
//            echo json_encode($error);
//        } else {
//            $data = array('upload_data' => $this->upload->data());
//            echo json_encode($data);
//        }



        /**

          $config['upload_path'] = 'upload/Main_category_product/';
          $path=$config['upload_path'];
          $config['allowed_types'] = 'gif|jpg|jpeg|png';
          $config['max_size'] = '1024';
          $config['max_width'] = '1920';
          $config['max_height'] = '1280';
          $this->load->library('upload');

          foreach ($_FILES as $key => $value)
          {


          if (!empty($key['name']))
          {


          $this->upload->initialize($config);
          if (!$this->upload->do_upload($key))
          {

          $errors = $this->upload->display_errors();


          flashMsg($errors);

          }
          else
          {
          // Code After Files Upload Success GOES HERE
          }
          }
          }

         * 
         */
    }

}

