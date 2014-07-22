<?php
date_default_timezone_set('Asia/Dhaka');
ini_set('max_execution_time', 50000);

App::uses('AppController', 'Controller');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MoLogsController
 *
 * @author Shakil
 */
class MoLogsController extends AppController{
    //put your code here    
    
    var $keywords = array('PTR', 'SUP', 'BR');
    var $occupations = array();
    
    public function beforeFilter(){
        parent::beforeFilter();
        $this->Auth->allow(array('add_survey'));
        
        $this->loadModel('Occupation');
        $this->Occupation->recursive = -1;
        $this->occupations = $this->Occupation->find('list', array('fields' => array('id','code')));
        
        $this->loadModel('Brand');
        $this->Brand->recursive = -1;
        $this->brands = $this->Brand->find('list', array('fields' => array('id','code')));
        //pr($this->occupations);exit;
    }
    
    /**
     * 
     */
    public function index(){
        $this->paginate = array('limit' => 100);
        $this->set('mo_logs',$this->paginate());
    }
    
    /**
     *
     * @param type $id 
     */
    public function delete( $id = null ){
        $this->MoLog->id = $id;
        $this->request->onlyAllow('post', 'delete');
        if( $this->MoLog->delete() ){
            $this->loadModel('Survey');
            $this->Survey->deleteAll(array('mo_log_id' => $id));
            $this->Session->setFlash(__('Mo Log delete successful!'));
            $this->redirect(array('controller' => 'MoLogs','action' => 'index'));
        }else{
            $this->Session->setFlash(__('Mo Log delete failed!'));
            $this->redirect(array('action' => 'index'));
        }
    }
    
    /**
     *
     * @return array $processed  
     */
    protected function _processing(){
        $mobile_number_temp = $_REQUEST['MSISDN'];
        $sms_text_temp = $_REQUEST['MSG'];
        $sms = $this->MoLog->sms_process($sms_text_temp);
        
        if( isset($_REQUEST['DATETIME']) && !empty($_REQUEST['DATETIME']) ){            
            $processed['created'] = $_REQUEST['DATETIME'];
            $processed['time_int'] = strtotime($processed['created']);
            $processed['date'] = date("Y-m-d", $processed['time_int']);
        }else{        
            $processed['date'] = date("Y-m-d");
            $processed['created'] = date("Y-m-d H:i:s");
            $processed['time_int'] = strtotime($processed['created']);
        }
        
        $processed['mobile_number'] = $this->MoLog->mobile_number_process($mobile_number_temp);

        $sms_slice = explode(' ', $sms);
        $processed['keyword'] = $sms_slice[0];
        
        $processed['lastMoLogId'] = $this->MoLog->save_log($processed['mobile_number'],$sms,$processed['keyword'],$processed['date'],$processed['time_int']);
        
        $processed['params'] = array();
        $processed['params'][0] = $processed['keyword'];
        
        $comma_sep_sms = substr($sms, strpos($sms,' '));
        
        $tok = strtok( $comma_sep_sms, ',\n');
        $tok = trim($tok);
        for(;1;){
            if($tok==false){
                break;
            }            
            $processed['params'][] = $tok;
            $tok = strtok(',\n');
            $tok = trim($tok);
        }
        $processed['params'][0] = isset($processed['params'][0]) ? strtoupper($processed['params'][0]) : 'XXX';
        
        return $processed;
    }
    
    /**
     * @desc Save and update 
     * @param type $rep_id
     * @param type $outlet_id
     * @param type $sec_id
     * @param type $sl_counter
     * @param type $processed['date'] 
     * @param int $sale_id if it is present then this method do update otherwise save
     */
    protected function _save_surveys($rep_id, $house_id, $rep_phone, $srv_counter=1, $created, $surv_detail, $survey_id = null){
        
        $data = array();
        if( $survey_id ){   
            $data['Survey']['id'] = $survey_id;
            $data['Survey']['modified'] = $created;
        }
        $data['Survey']['campaign_id'] = $this->current_campaign_detail['Campaign']['id'];
        $data['Survey']['representative_id'] = $rep_id;
        $data['Survey']['rep_phone'] = $rep_phone;
        $data['Survey']['house_id'] = $house_id;
        $data['Survey']['survey_counter'] = $srv_counter; 
        
        foreach($surv_detail as $k => $v ){
            $data['Survey'][$k] = $v;
        }        
//        echo '<pre>';print_r($data);exit;
        
        if( !$survey_id ){
            $this->Survey->create();
        }        
        return $this->Survey->save($data);
    }
    
    /**
     * Check the sale message format, product codes validity and also format and array for sale detail
     */
    protected function _format_survey( $params, $survey_id = null, $survey_counter = 1, $moLogId, $surveyDetails = array(), $keyWord = '' ){
        
        $data = array();
        
        if( $survey_id ){
            //if $survey_counter != $surveyDetails[0]['surveys']['survey_counter'] then definately wrong. 
            if( $survey_counter != $surveyDetails[0]['surveys']['survey_counter'] ){
                $data['error'] = 'Sorry! You already inserted values for this customer.';
                return $data;
            }else{
                $data['Survey']['survey_counter'] = $survey_counter;
            }
        }
        //When the data is coming from PTR rather SUP
        if( $keyWord=='PTR' ){
                //checking Age
            if( !is_numeric($params[4]) ){
                $data['error'] = 'Sorry! Invalid Age';
                return $data;
            }else{
                $data['Survey']['age'] = $params[4];
            }

            //checking occupations code       
            $valid = false;
            $occp_id = 0;
            foreach($this->occupations as $k => $ocp ){
                if( $ocp == $params[5] ){
                    $valid = true;
                    $occp_id = $k;
                    break;
                }
            }
            if( !$valid ){
                $data['error'] = 'Sorry! Invalid Occupation code!';
                return $data;
            }else{
                $data['Survey']['occupation_id'] = $occp_id;
            }

            //checking brand code       
            $valid = false;
            $brnd_id = 0;
            foreach($this->brands as $k => $br ){
                if( $br == $params[6] ){
                    $valid = true;
                    $brnd_id = $k;
                    break;
                }
            }
            if( !$valid ){
                $data['error'] = 'Sorry! Invalid Brand code!';
                return $data;
            }else{
                $data['Survey']['brand_id'] = $brnd_id;
            }

            //check mobile no
            if( strlen($params[3])<11 || strlen($params[3])>11 ){
                $data['error'] = 'Sorry! Consumer phone number must be of 11 digits. Please try again with right number.';
                return $data;
            }
            $data['Survey']['name'] = $params[2];
            $data['Survey']['phone'] = $params[3];
            
        }else if( $keyWord=='SUP'){
            //check mobile no
            if( strlen($params[2])<11 || strlen($params[2])>11 ){
                $data['error'] = 'Sorry! Consumer phone number must be of 11 digits. Please try again with right number.';
                return $data;
            }
            
            //checking the date format
            if( date('Y-m-d', strtotime($params[3])) != $params[3] ){
                $data['error'] = 'Sorry! Invalid date format. Please write the date format as yyyy-mm-dd and try again.';
                return $data;
            }
            $data['Survey']['phone'] = $params[2];
            $data['Survey']['is_sup'] = 1;
            $data['Survey']['permission_slip_date'] = $params[3];
            $data['Survey']['is_right'] = $params[4]=='R'?1:0;
            
        }//sms format br, brid, outlet id code or name, date, mobile no, amount, counter
        else if( $keyWord=='BR' ){
            //check mobile no
            if( strlen($params[4])<11 || strlen($params[4])>11 ){
                $data['error'] = 'Sorry! Consumer phone number must be of 11 digits. Please try again with right number.';
                return $data;
            }
            
            //checking the date format
            if( date('Y-m-d', strtotime($params[3])) != $params[3] ){
                $data['error'] = 'Sorry! Invalid date format. Please write the date format as yyyy-mm-dd and try again.';
                return $data;
            }
            $data['Survey']['phone'] = $params[4];            
            $data['Survey']['permission_slip_date'] = $params[3];
            $data['Survey']['amount'] = $params[5];
            $data['Survey']['outlet'] = $params[2];
            $data['Survey']['is_br'] = 1;
        }
        
        
        $data['Survey']['mo_log_id'] = $moLogId;
        
        return $data;
    }
    
    /**
     *
     * @param type $repId
     * @param type $customer_phone_no
     * @param type $msg_counter
     * @return boolean 
     */
    protected function _is_update( $repId, $customer_phone_no, $msg_counter = 0, $keyWord = '' ){
        //when checking the supervisers sms
        if( $keyWord=='SUP' ){
            $qry = 'SELECT surveys.id, surveys.survey_counter FROM surveys '.
                        'WHERE surveys.representative_id='.$repId.
                        ' AND surveys.campaign_id='.$this->current_campaign_detail['Campaign']['id'].
                        ' AND surveys.survey_counter='.$msg_counter.
                        ' AND surveys.is_sup=1'.
                        ' AND DATE(surveys.created)="'.date('Y-m-d').'"';
            $res = $this->MoLog->query($qry);

            if( count($res)>0 ){
                return $res;
            }
            return false;
        }//when checking the representatives sms
        else if( $keyWord=='PTR'){
            $qry = 'SELECT surveys.id, surveys.survey_counter FROM surveys '.
                        'WHERE surveys.representative_id='.$repId.
                        ' AND surveys.campaign_id='.$this->current_campaign_detail['Campaign']['id'].
                        ' AND surveys.survey_counter='.$msg_counter.
                        ' AND surveys.is_sup=0 AND surveys.is_br=0'.
                        ' AND DATE(surveys.created)="'.date('Y-m-d').'"';
            $res = $this->MoLog->query($qry);

            if( count($res)>0 ){
                return $res;
            }
            return false;
            
        }else if( $keyWord=='BR' ){ //when the keyword is BR
            $qry = 'SELECT surveys.id, surveys.survey_counter FROM surveys '.
                        'WHERE surveys.representative_id='.$repId.
                        ' AND surveys.campaign_id='.$this->current_campaign_detail['Campaign']['id'].
                        ' AND surveys.survey_counter='.$msg_counter.
                        ' AND surveys.is_br=1'.
                        ' AND DATE(surveys.created)="'.date('Y-m-d').'"';
            $res = $this->MoLog->query($qry);
            
//            pr($res);exit;

            if( count($res)>0 ){
                return $res;
            }
            return false;
        }
    }
    
    //check whether the received sms is in campaigns working hours or not
    protected function _is_in_working_hours(){
        $current_hour_min = date('H:i');
        
        if( strcmp($this->current_campaign_detail['Campaign']['start_time'], $current_hour_min) <=0 &&
            strcmp($this->current_campaign_detail['Campaign']['end_time'], $current_hour_min)>=0 ){
            return true;
        }
        return false;
    }
    
        
    /**
     * This processes add and update sales request
     */
    public function add_survey(){
        
        //var_dump($this->current_campaign_detail);exit;
        
        $this->layout = $this->autoRender = false;
        $processed = $this->_processing();
//        pr($processed);exit;
        $errorFound = true;
        
        //check whether the received message is sent during campaign time or not
        if( !$this->_is_in_working_hours() ){
            $error = 'Campaign has been finished for today. Please try again tomorrow within campaign time ('.$this->current_campaign_detail['Campaign']['start_time'].' to '.
                    $this->current_campaign_detail['Campaign']['end_time'].')';
        }else{
            $ttl_msg_part = count($processed['params']);
            
            if( $processed['params'][0]!='PTR' && $processed['params'][0]!='SUP'
               && $processed['params'][0]!='BR'){
                $error = "Your keyword is wrong, please try again with right keywork.";
            }
            else if( $processed['params'][0]=='PTR' && (!is_numeric($processed['params'][$ttl_msg_part-1]) || 
                $ttl_msg_part != 8) ) {

                $error = "Your SMS format is wrong, plesae try again with right format.";            
            }else if( $processed['params'][0]=='SUP' && (!is_numeric($processed['params'][$ttl_msg_part-1]) || 
                $ttl_msg_part != 6) ){
                $error = "Your SMS format is wrong, plesae try again with right format.";            
                
            }else if( $processed['params'][0]=='BR' && (!is_numeric($processed['params'][$ttl_msg_part-1]) || 
                $ttl_msg_part != 7 || !is_numeric($processed['params'][$ttl_msg_part-2]))){
                $error = "Your SMS format is wrong, plesae try again with right format.";            
            }
            else if( strlen($processed['mobile_number']) <13 || strlen($processed['mobile_number'])>13 ){
                $error = 'Sorry! Your mobile number is invalid.';
            }else{                           
                $repId = $this->MoLog->check_rep_br_code( $processed['params'][1]);

                //pr($repId);exit;

                if( !is_array($repId) ){
                    $error = 'Invalid PTR code! Please try again with valid code.';                    
                }else{                    
                    $this->loadModel('Survey');
                    
                    //when the keyword is 'PTR'
                    if($processed['params'][0]=='PTR' ){
                        
                        $res = $this->_is_update($repId[0]['representatives']['id'], $processed['params'][3], $processed['params'][7], 'PTR');

                        if( isset($res['error']) ){
                            $error = $res['error'];
                        }else if(count($res)>0 && $res!=false) { 
                            $survey_detail = $this->_format_survey($processed['params'], $res[0]['surveys']['id'], $processed['params'][ $ttl_msg_part - 1 ], $processed['lastMoLogId'], $res, 'PTR');

                            if( isset($survey_detail['error']) ){   
                                $error = $survey_detail['error'];
                            }else{
                                $this->_save_surveys($repId[0]['representatives']['id'], $repId[0]['representatives']['house_id'],
                                        $processed['mobile_number'], $processed['params'][$ttl_msg_part-1], $processed['created'], 
                                        $survey_detail['Survey'], $res[0]['surveys']['id']);   

                                $errorFound = false;
                                $msg = 'Thank you! Your message have been updated.';
                            }
                        }
                        else {
                            $survey_detail = $this->_format_survey($processed['params'], null, $processed['params'][ $ttl_msg_part - 1 ], $processed['lastMoLogId'], array(), 'PTR');

                            if( isset($survey_detail['error']) ){                    
                                $error = $survey_detail['error'];
                            }else{                            
                                $this->_save_surveys($repId[0]['representatives']['id'], $repId[0]['representatives']['house_id'],
                                        $processed['mobile_number'], $processed['params'][$ttl_msg_part-1], $processed['created'],
                                        $survey_detail['Survey']);//                    

                                $errorFound = false;
                                $msg = 'Thank you! Your message have been received.';

                                $this->loadModel('Achievement');
                                $this->Achievement->increment_chievement($repId[0]['representatives']['house_id'], $this->current_campaign_detail['Campaign']['id']);
                            }
                        }
                    }//when the keyword is 'SUP'
                    else if($processed['params'][0]=='SUP' ){
                        $res = $this->_is_update($repId[0]['representatives']['id'],
                                $processed['params'][2], $processed['params'][5], 'SUP');

                        if( isset($res['error']) ){
                            $error = $res['error'];
                        }else if(count($res)>0 && $res!=false) { 
                            $survey_detail = $this->_format_survey($processed['params'], $res[0]['surveys']['id'],
                                    $processed['params'][ $ttl_msg_part - 1 ], $processed['lastMoLogId'], $res, 'SUP');

                            if( isset($survey_detail['error']) ){   
                                $error = $survey_detail['error'];
                            }else{
                                $this->_save_surveys($repId[0]['representatives']['id'], $repId[0]['representatives']['house_id'],
                                        $processed['mobile_number'], $processed['params'][$ttl_msg_part-1], $processed['created'], 
                                        $survey_detail['Survey'], $res[0]['surveys']['id']);   

                                $errorFound = false;
                                $msg = 'Thank you! Your message have been updated.';
                            }
                        }
                        else {
                            $survey_detail = $this->_format_survey($processed['params'], null, 
                                    $processed['params'][ $ttl_msg_part - 1 ], $processed['lastMoLogId'], array(), 'SUP');

                            if( isset($survey_detail['error']) ){                    
                                $error = $survey_detail['error'];
                            }else{                            
                                $this->_save_surveys($repId[0]['representatives']['id'], $repId[0]['representatives']['house_id'],
                                        $processed['mobile_number'], $processed['params'][$ttl_msg_part-1], $processed['created'],
                                        $survey_detail['Survey']);//                    

                                $errorFound = false;
                                $msg = 'Thank you! Your message have been received.';
                            }
                        }
                    }else if($processed['params'][0]=='BR' ){//when the keyword is BR
                        $res = $this->_is_update($repId[0]['representatives']['id'],
                                $processed['params'][4], $processed['params'][6], 'BR');

                        if( isset($res['error']) ){
                            $error = $res['error'];
                        }else if(count($res)>0 && $res!=false) { 
                            $survey_detail = $this->_format_survey($processed['params'], $res[0]['surveys']['id'],
                                    $processed['params'][ $ttl_msg_part - 1 ], $processed['lastMoLogId'], $res, 'BR');

                            if( isset($survey_detail['error']) ){   
                                $error = $survey_detail['error'];
                            }else{
                                $this->_save_surveys($repId[0]['representatives']['id'], $repId[0]['representatives']['house_id'],
                                        $processed['mobile_number'], $processed['params'][$ttl_msg_part-1], $processed['created'], 
                                        $survey_detail['Survey'], $res[0]['surveys']['id']);   

                                $errorFound = false;
                                $msg = 'Thank you! Your message have been updated.';
                            }
                        }
                        else {
                            $survey_detail = $this->_format_survey($processed['params'], null, 
                                    $processed['params'][ $ttl_msg_part - 1 ], $processed['lastMoLogId'], array(), 'BR');

                            if( isset($survey_detail['error']) ){                    
                                $error = $survey_detail['error'];
                            }else{                            
                                $this->_save_surveys($repId[0]['representatives']['id'], $repId[0]['representatives']['house_id'],
                                        $processed['mobile_number'], $processed['params'][$ttl_msg_part-1], $processed['created'],
                                        $survey_detail['Survey']);//                    

                                $errorFound = false;
                                $msg = 'Thank you! Your message have been received.';
                            }
                        }
                    }
                }
            }
        }
        if( $errorFound ){
            if( isset($_REQUEST['DATETIME']) && !empty($_REQUEST['DATETIME']) ){
                echo $error.'<br />';
            }else{
                $this->MoLog->send_sms_free_of_charge($processed['mobile_number'], $error, 796, $processed['keyword'], $processed['date'], $processed['time_int']);
            }            
            die();
        }else{
            if( isset($_REQUEST['DATETIME']) && !empty($_REQUEST['DATETIME']) ){
                echo $msg.'<br />';
            }else{
                $this->MoLog->send_sms_free_of_charge($processed['mobile_number'], $msg, 796, $processed['keyword'], $processed['date'], $processed['time_int']);
            }            
            die();
        }
    }
    
    /**
     * @desc Suppose server was off for a day. In that case through this method all the sms stored in a xls
     * file can be restored into database in proper way. 
     */
    public function import_backup(){
        if( $this->request->is('post') ){
            if( !empty($this->request->data['MoLog']['backup_xls']) ){
                if( $this->request->data['MoLog']['backup_xls']['error']==0){
                    $renamed_f_name = time().$this->request->data['MoLog']['backup_xls']['name'];
                    if( move_uploaded_file($this->request->data['MoLog']['backup_xls']['tmp_name'], WWW_ROOT.$renamed_f_name) ){

                        $this->_import($renamed_f_name);

                    }else{
                        $this->Session->setFlash(__('File upload failed! Please try again.'));
                    }
                }else{
                    $this->Session->setFlash(__('Your given file is corrupted! Please try with valid file.'));
                }
            }else{
                $this->Session->setFlash(__('You have not selected any file to upload.'));
            }
        }
    }
    
    /**
     *
     * @param type $fName 
     */
    protected function _import( $xlName ){
        
        App::import('Vendor','PHPExcel',array('file' => 'PHPExcel/Classes/PHPExcel.php'));

        //here i used microsoft excel 2007
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        //set to read only
        $objReader->setReadDataOnly(true);
        //load excel file
        $objPHPExcel = $objReader->load($xlName);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        $totalRow = $objPHPExcel->getActiveSheet()->getHighestRow();

        //pr($totalRow);exit;
        $ch = curl_init();

        for($i=2; $i<=$totalRow; $i++){ 
            $data['MSISDN'] = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
            $data['MSG'] = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
            $data['DATETIME'] = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
            
            $sms_slice = explode(' ', $data['MSG']);
            
            if( $sms_slice[0]=='PSTT' ){
                $url = Configure::read('base_url').'sms_pstt.php';
            }else if( $sms_slice[0]=='CUP' ){
                $url = Configure::read('base_url').'sms_cup.php';
            }else if( $sms_slice[0]=='RP' ){
                $url = Configure::read('base_url').'sms_rp.php';
            }            
            curl_setopt($ch, CURLOPT_URL,$url);
            //curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
            $response = curl_exec($ch);
        }
        curl_close($ch);
    }
}