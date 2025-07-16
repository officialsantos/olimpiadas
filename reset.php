<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	include 'includes/session.php';

	if(isset($_POST['reset'])){
		$email = $_POST['email'];

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email=:email");
		$stmt->execute(['email'=>$email]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			//generate code
			$set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$code=substr(str_shuffle($set), 0, 15);
			try{
				$stmt = $conn->prepare("UPDATE users SET reset_code=:code WHERE id=:id");
				$stmt->execute(['code'=>$code, 'id'=>$row['id']]);
				
				$message = "
					<h2>Reinicio de Contraseña</h2>
					<p>Por favor, cliquee en el enlace para reestablecer tu contraseña.</p>
					<p>Si no fue usted, sus credenciales pueden estar en peligro. Contáctese con el soporte de PatagoniaViajes.</p>
					<a href='https://patagoniaviajes.infinityfreeapp.com/password_reset.php?code=".$code."&user=".$row['id']."'>Reiniciar Contraseña</a>
				";

				//Load phpmailer
	    		require 'vendor/autoload.php';

	    		$mail = new PHPMailer(true);                             
			    try {
			        //Server settings
			        $mail->isSMTP();                                     
			        $mail->Host = 'smtp.gmail.com';                      
			        $mail->SMTPAuth = true;                               
			        $mail->Username = 'sanchuap@gmail.com';     
			        $mail->Password = 'password';                    
			        $mail->SMTPOptions = array(
			            'ssl' => array(
			            'verify_peer' => false,
			            'verify_peer_name' => false,
			            'allow_self_signed' => true
			            )
			        );                         
			        $mail->SMTPSecure = 'tls';                           
			        $mail->Port = 587;                                   

			        $mail->setFrom('sanchuap@gmail.com');
			        
			        //Recipients
			        $mail->addAddress($email);              
			        $mail->addReplyTo('sanchuap@gmail.com');
			       
			        //Content
			        $mail->isHTML(true);                                  
			        $mail->Subject = 'Recupera tu cuenta de PatagoniaViajes';
			        $mail->Body    = $message;

			        $mail->send();

			        $_SESSION['success'] = 'Enlace de reinicio de contraseña enviado';
			     
			    } 
			    catch (Exception $e) {
			        $_SESSION['error'] = 'El enlace no pudo ser enviado. Mailer Error: '.$mail->ErrorInfo;
			    }
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
		}
		else{
			$_SESSION['error'] = 'Email no encontrado';
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Correo seleccionado ya asociado con su cuenta';
	}

	header('location: password_forgot.php');

?>
