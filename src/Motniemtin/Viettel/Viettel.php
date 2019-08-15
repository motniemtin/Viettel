<?php
namespace Motniemtin\Viettel;

use Motniemtin\Viettel\Auto;
use Exception;

class Viettel{
  var $auto,$phone,$password;
  var $post;
  public function __construct($phone,$password){
    if(!is_dir("cookie")){
      mkdir("cookie",777);
    }
    $this->auto=new Auto(1,"cookie/".$phone.".viettel.cookie");
    //$this->auto=new Auto(1,"viettel.cookie");
    $this->phone=$phone;
    $this->password=$password;
  }
  public function getInformation(){
    $html=$this->auto->Get('https://viettel.vn/my-viettel/quan-ly-tai-khoan');
    if(substr_count($html, 'user_alias_username')==0 || substr_count($html, 'user_alias_phonenumber')==0){
      $html=$this->auto->Get('https://viettel.vn/dang-nhap');
      $_csrf_token=$this->auto->SCodeOne('vt_signin[_csrf_token]" value="','"',$html);
      $post=array('vt_signin' => array('_csrf_token' => $_csrf_token, 'category' => 'mob', 'username' => $this->phone, 'password' => $this->password));
      $postdata= http_build_query($post);    
      //print_r($postdata);
      $html=$this->auto->Post('https://viettel.vn/dang-nhap',$postdata,'https://viettel.vn/dang-nhap');
      $html=$this->auto->Get('https://viettel.vn/my-viettel/quan-ly-tai-khoan');    
    }  
    $result=array();
    if(substr_count($html, 'user_alias_username')!=0 && substr_count($html, 'user_alias_phonenumber')!=0){
      //stay login!
      $tai_khoan=$this->auto->SCodeOne('Tài khoản chính:','<',$html);
      if($tai_khoan==''){
        $html=$this->auto->Get('https://viettel.vn/my-viettel/quan-ly-tai-khoan');   
      }
      $dung_luong_con_lai=$this->auto->SCodeOne('Dung lượng còn lại:','<',$html);
      if($dung_luong_con_lai==''){
        $html=$this->auto->Get('https://viettel.vn/my-viettel/quan-ly-tai-khoan');   
      }
      $result['status']='ok';
      $section=$this->auto->SCodeOne('<section class="section-63">','</section>',$html);
      if(substr_count($section,'Thông tin tài khoản')!=0){
        $goi_cuoc=$this->auto->SCodeOne('Gói cước','<',$section);
        $goi_cuoc=trim($goi_cuoc);
        $tai_khoan=$this->auto->SCodeOne('Tài khoản chính:','<',$section);
        $tai_khoan=trim($tai_khoan);
        $goi_data=$this->auto->SCodeOne('Gói data','<',$section);
        $goi_data=trim($goi_data);
        $dung_luong_con_lai=$this->auto->SCodeOne('Dung lượng còn lại:','<',$section);
        $dung_luong_con_lai=trim($dung_luong_con_lai);
        $han_su_dung=$this->auto->SCodeOne('Hạn sử dụng:','<',$section);
        $han_su_dung=trim($han_su_dung);
        $result['type']=$goi_cuoc;
        $result['money']=$tai_khoan;
        $result['data_type']=$goi_data;
        $result['data_remain']=$dung_luong_con_lai;
        $result['data_expiry']=$han_su_dung;
        $result['updated_at']=date("d/m/Y");
      }else{
        $result['info']='Đã đăng nhập nhưng không thể tìm thấy thông tin tài khoản !';
        $result['status']='error';
      }
    }else{
      $result['status']='error';
      $result['info']='Không thể đăng nhập hệ thống !';
    }
    //print_r($result);
    return $result;
  }
  public function getOTP(){    
    $this->post=array();
    $html=$this->auto->Get('https://viettel.vn/dang-ky');
    file_put_contents("/var/www/nalico.com.vn/public_html/test.html",$html);
    
    $this->post['_csrf_token']=$this->auto->SCodeOne('name="_csrf_token" value="','"',$html);
    $this->post['registerForm[_csrf_token]']=$this->auto->SCodeOne('name="registerForm[_csrf_token]" value="','"',$html);
    $this->post['registerForm1[_csrf_token]']=$this->auto->SCodeOne('name="registerForm1[_csrf_token]" value="','"',$html);
    $this->post['registerForm2[_csrf_token]']=$this->auto->SCodeOne('name="registerForm2[_csrf_token]" value="','"',$html);
    $this->post['registerForm3[_csrf_token]']=$this->auto->SCodeOne('name="registerForm3[_csrf_token]" value="','"',$html);
    $this->post['registerForm4[_csrf_token]']=$this->auto->SCodeOne('name="registerForm4[_csrf_token]" value="','"',$html);
    $this->post['registerForm5[_csrf_token]']=$this->auto->SCodeOne('name="registerForm5[_csrf_token]" value="','"',$html);
    $this->post['registerForm[category]']=1;
    
    $this->post['registerForm1[subscriber_type]']=1;
    $this->post['registerForm1[username]']=$this->phone;
    $this->post['registerForm1[password]']=$this->password;
    $this->post['registerForm1[password_again]']=$this->password;
    $this->post['registerForm1[otp]']=''; //fill here..
    
    $this->post['registerForm2[subscriber_type]']='A';
    $this->post['registerForm2[register_type]']='OTP';
    $this->post['registerForm2[username]']='';
    $this->post['registerForm2[password]']='';
    $this->post['registerForm2[password_again]']='';
    $this->post['registerForm2[password_of_package]']='';
    $this->post['registerForm2[region]']='1';
    $this->post['registerForm2[otp]']='';
    
    $this->post['registerForm3[username]']='';
    $this->post['registerForm3[password]']='';
    $this->post['registerForm3[password_again]']='';
    $this->post['registerForm3[otp]']='';
    
    $this->post['registerForm4[register_type]']='1';
    $this->post['registerForm4[username]']='';
    $this->post['registerForm4[password]']='';
    $this->post['registerForm4[password_again]']='';
    $this->post['registerForm4[serial]']='';
    $this->post['registerForm4[otp]']='';
    
    $this->post['registerForm5[subscriber_type]']='K';
    $this->post['registerForm5[username]']='';
    $this->post['registerForm5[password]']='';
    $this->post['registerForm5[password_again]']='';
    $this->post['registerForm5[otp]']='';
    $this->post['registerForm[captcha]']='';
    $this->post['captcha_code']=$this->auto->SCodeOne("src='/captcha?","'",$html);
    
    //echo "https://viettel.vn/get-register-otp?category=1&username=".$this->phone."&accountType=1&csrfToken=".$this->post['_csrf_token']."&_=".(time()*1000+rand(100,999));
    $json=$this->auto->xmlGet("https://viettel.vn/get-register-otp?category=1&username=".$this->phone."&accountType=1&csrfToken=".$this->post['_csrf_token']."&_=".(time()*1000+rand(100,999)));
    if(trim($json)==""){
      $json=$this->auto->xmlGet("https://viettel.vn/get-register-otp?category=1&username=".$this->phone."&accountType=1&csrfToken=".$this->post['_csrf_token']."&_=".(time()*1000+rand(100,999)));
    }
    if(trim($json)==""){
      $json=$this->auto->xmlGet("https://viettel.vn/get-register-otp?category=1&username=".$this->phone."&accountType=1&csrfToken=".$this->post['_csrf_token']."&_=".(time()*1000+rand(100,999)));
    }
    $result=array();
    $json=json_decode($json,1);
    if(!isset($json['errorCode']) || !$json['message']){      
      $result['status']='error';
      $result['info']="Can't not send otp code";      
    }else{
      if($json['errorCode']=="0" && $json['message']=='Mã xác nhận đã được gửi đến số thuê bao của Quý khách.'){
        //echo "Register started, has sent otp code";
        $result=array();
        $result['status']='ok';
        $result['info']="Register started, has sent otp code";
        //return $result;
      }else{
        $result=array();
        $result['status']='error';
        if(isset($json['message'])){
          $result['info']=$json['message'];
        }else{
          $result['info']="Unknown";
        }        
      }   
    } 
    file_put_contents("post/".$this->phone.".json",json_encode($this->post));
    print_r($result);
    return $result;
  }
  public function registerAccount($otp){
    if(file_exists("post/".$this->phone.".json")){
      $this->post=json_decode("post/".$this->phone.".json",1);      
    }
    $this->post['registerForm1[otp]']=$otp;
    $captcha=$this->auto->Get("https://viettel.vn//captcha?".$this->post['captcha_code']);
    file_put_contents("post/".$this->phone.".png",file_get_contents("https://viettel.vn//captcha?".$this->post['captcha_code']));
    
  }
  public function changePassword($newPassword){
    
  }  
  function solveCaptcha($file_path){
    
  }
}