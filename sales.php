<?php
	include 'includes/session.php';
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	if(isset($_GET['pay'])){
		$payid = $_GET['pay'];
		$date = date('Y-m-d');

		$conn = $pdo->open();
		
		try{
			
			$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
			$stmt->execute(['user_id'=>$user['id'], 'pay_id'=>$payid, 'sales_date'=>$date]);
			$salesid = $conn->lastInsertId();
			
			try{
				$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
				$stmt->execute(['user_id'=>$user['id']]);

				foreach($stmt as $row){
					$insert = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
					$insert->execute(['sales_id'=>$salesid, 'product_id'=>$row['product_id'], 'quantity'=>$row['quantity']]);
				}

				$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
				$stmt->execute(['user_id'=>$user['id']]);

				$_SESSION['success'] = 'Transacción exitosa. Gracias.<br> Se envió una notificación a su correo electrónico.';
				
				try{
					$email = $user['email'];
					$message = "
						<h2>Gracias por realizar tu compra.</h2>
						<p>Por seguridad, todos los datos son almacenados en tu perfil, en el Historial de Transacciones.</p>
						<p>Puede cliquear en el enlace para ver sus datos automáticamente en nuestro sitio web.</p>
						<a href='https://patagoniaviajes.infinityfreeapp.com/profile.php?sales_id=".$salesid."'>Ver mi compra</a>
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
				        $mail->Subject = 'Gracias por elegir PatagoniaViajes';
				        $mail->Body    = $message;

				        $mail->send();

				    } 
				    catch (Exception $e) {
				        $_SESSION['error'] = 'El mensaje no pudo ser enviado. Mailer Error: '.$mail->ErrorInfo;
				    }
				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
				}
			

		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}

		$pdo->close();
	}
 catch (PDOException $e1) {
$_SESSION['error'] = $e1->getMessage();
 }
	}


	header('location: profile.php');
	
?>
