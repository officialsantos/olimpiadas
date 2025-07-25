<?php include 'includes/session.php'; ?>
<?php
	if(!isset($_SESSION['user'])){
		header('location: index.php');
		exit();
	}
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<?php
	        			if(isset($_SESSION['error'])){
	        				echo "
	        					<div class='callout callout-danger'>
	        						".$_SESSION['error']."
	        					</div>
	        				";
	        				unset($_SESSION['error']);
	        			}

	        			if(isset($_SESSION['success'])){
	        				echo "
	        					<div class='callout callout-success'>
	        						".$_SESSION['success']."
	        					</div>
	        				";
	        				unset($_SESSION['success']);
	        			}
	        		?>
	        		<div class="box box-solid">
	        			<div class="box-body">
	        				<div class="col-sm-3">
	        					<img src="<?php echo (!empty($user['photo'])) ? 'images/'.$user['photo'] : 'images/profile.jpg'; ?>" width="100%">
	        				</div>
	        				<div class="col-sm-9">
	        					<div class="row">
	        						<div class="col-sm-3">
	        							<h4>Nombre:</h4>
	        							<h4>Email:</h4>
	        							<h4>Info. de Contacto:</h4>
	        							<h4>Dirección:</h4>
	        							<h4>Miembro desde:</h4>
	        						</div>
	        						<div class="col-sm-9">
	        							<h4><?php echo htmlspecialchars($user['firstname'].' '.$user['lastname']); ?>
	        								<span class="pull-right">
	        									<a href="#edit" class="btn btn-success btn-flat btn-sm" data-toggle="modal"><i class="fa fa-edit"></i> Editar</a>
	        								</span>
	        							</h4>
	        							<h4><?php echo htmlspecialchars($user['email']); ?></h4>
	        							<h4><?php echo (!empty($user['contact_info'])) ? htmlspecialchars($user['contact_info']) : 'N/A'; ?></h4>
	        							<h4><?php echo (!empty($user['address'])) ? htmlspecialchars($user['address']) : 'N/A'; ?></h4>
	        							<h4><?php echo date('M d, Y', strtotime($user['created_on'])); ?></h4>
	        						</div>
	        					</div>
	        				</div>
	        			</div>
	        		</div>

	        		<!-- Historial de Transacciones -->
	        		<div class="box box-solid">
	        			<div class="box-header with-border">
	        				<h4 class="box-title"><i class="fa fa-calendar"></i> <b>Historial de Transacciones</b></h4>
	        			</div>
	        			<div class="box-body">
	        				<table class="table table-bordered" id="example1">
	        					<thead>
	        						<th class="hidden"></th>
	        						<th>Fecha</th>
	        						<th>Transacción#</th>
	        						<th>Total pagado</th>
	        						<th>Detalles Completos</th>
	        					</thead>
	        					<tbody>
	        					<?php
	        						$conn = $pdo->open();

	        						try{
	        							$stmt = $conn->prepare("SELECT * FROM sales WHERE user_id=:user_id ORDER BY sales_date DESC");
	        							$stmt->execute(['user_id'=>$user['id']]);
	        							foreach($stmt as $row){
	        								$stmt2 = $conn->prepare("SELECT * FROM details LEFT JOIN products ON products.id=details.product_id WHERE sales_id=:id");
	        								$stmt2->execute(['id'=>$row['id']]);
	        								$total = 0;
	        								foreach($stmt2 as $row2){
	        									$subtotal = $row2['price']*$row2['quantity'];
	        									$total += $subtotal;
	        								}
	        								echo "
	        									<tr>
	        										<td class='hidden'></td>
	        										<td>".date('M d, Y', strtotime($row['sales_date']))."</td>
	        										<td>".htmlspecialchars($row['pay_id'])."</td>
	        										<td>".htmlspecialchars($row['total_paid'])."</td>
	        										<td><button class='btn btn-sm btn-flat btn-info transact' data-id='".$row['id']."'><i class='fa fa-search'></i> Ver</button></td>
	        									</tr>
	        								";
	        							}

	        						}
        							catch(PDOException $e){
										echo "Hubo un problema en la conexión: " . $e->getMessage();
									}

	        						$pdo->close();
	        					?>
	        					</tbody>
	        				</table>
	        			</div>
	        		</div>
					<!-- Modal para editar perfil -->
					<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="editLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
						<form method="POST" action="profile_edit.php" enctype="multipart/form-data">
							<div class="modal-header">
							<h4 class="modal-title" id="editLabel">Editar Perfil</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
								<span aria-hidden="true">&times;</span>
							</button>
							</div>
							<div class="modal-body">
							<!-- Campo oculto para enviar el "edit" -->
							<input type="hidden" name="edit" value="1">
							
							<div class="form-group">
								<label for="firstname">Nombre</label>
								<input type="text" class="form-control" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
							</div>

							<div class="form-group">
								<label for="lastname">Apellido</label>
								<input type="text" class="form-control" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
							</div>

							<div class="form-group">
								<label for="email">Correo</label>
								<input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
							</div>

							<div class="form-group">
								<label for="contact">Información de contacto</label>
								<input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($user['contact_info']); ?>">
							</div>

							<div class="form-group">
								<label for="address">Dirección</label>
								<input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
							</div>

							<div class="form-group">
								<label for="password">Nueva Contraseña</label>
								<input type="password" class="form-control" name="password" placeholder="Dejar en blanco para no cambiar">
							</div>

							<div class="form-group">
								<label for="curr_password">Contraseña actual</label>
								<input type="password" class="form-control" name="curr_password" required>
							</div>

							<div class="form-group">
								<label for="photo">Foto de perfil</label>
								<input type="file" class="form-control" name="photo">
							</div>
							</div>
							<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
							<button type="submit" class="btn btn-primary">Guardar Cambios</button>
							</div>
						</form>
						</div>
					</div>
					</div>


	        		<!-- Reservaciones Pendientes -->
	        		<div class="box box-solid">
	        			<div class="box-header with-border">
	        				<h4 class="box-title"><i class="fa fa-calendar-check-o"></i> <b>Reservaciones Pendientes</b></h4>
	        			</div>
	        			<div class="box-body">
	        				<table class="table table-bordered" id="reservations_table">
	        					<thead>
	        						<th>Paquete</th>
	        						<th>Cantidad</th>
	        						<th>Duración (días)</th>
	        						<th>Monto a Pagar</th>
	        						<th>Fecha de Reserva</th>
	        						<th>Estado</th>
	        						<th>Acciones</th>
	        					</thead>
	        					<tbody>
	        					<?php
	        						$conn = $pdo->open();

									try {
										$stmt_cancel = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE status = 'pending' AND expire_at <= NOW()");
										$stmt_cancel->execute();
									} catch (PDOException $e) {
										// Puedes registrar el error si deseas
									}

	        						try {
	        							$stmt = $conn->prepare("SELECT r.id, p.name, r.quantity, r.duration_days, r.reserved_at, r.status FROM reservations r JOIN products p ON r.product_id = p.id WHERE r.user_id=:user_id ORDER BY r.reserved_at DESC");
	        							$stmt->execute(['user_id'=>$user['id']]);
	        							foreach($stmt as $res) {
	        								// Obtener precio unitario del producto
	        								$stmt_price = $conn->prepare("SELECT price FROM products WHERE id = (SELECT product_id FROM reservations WHERE id = :res_id)");
	        								$stmt_price->execute(['res_id' => $res['id']]);
	        								$product_price = $stmt_price->fetchColumn();

	        								$monto_pagar = $product_price * $res['quantity'] * $res['duration_days'];

	        								echo "
	        									<tr>
	        										<td>".htmlspecialchars($res['name'])."</td>
	        										<td>".(int)$res['quantity']."</td>
	        										<td>".(int)$res['duration_days']."</td>
	        										<td>&#36; ".number_format($monto_pagar, 2)."</td>
	        										<td>".date('d/m/Y', strtotime($res['reserved_at']))."</td>
	        										<td>".ucfirst($res['status'])."</td>
	        										<td>
	        								";
	        								if($res['status'] == 'pending'){
	        									echo "
	        										<a href='edit_reservation.php?id=".$res['id']."' class='btn btn-primary btn-sm'>Modificar</a>
	        										<form method='POST' action='cancel_reservation.php' style='display:inline-block' onsubmit='return confirm(\"¿Estás seguro de cancelar esta reservación?\");'>
	        											<input type='hidden' name='reservation_id' value='".$res['id']."'>
	        											<button type='submit' class='btn btn-danger btn-sm'>Cancelar</button>
	        										</form>
													<a href='pay_reservation.php?id=".$res['id']."' class='btn btn-success btn-sm'>Pagar</a>
	        									";
	        								} else {
	        									echo "<em>No disponible</em>";
	        								}
	        								echo "
	        										</td>
	        									</tr>
	        								";
	        							}

	        						} catch(PDOException $e) {
	        							echo "<tr><td colspan='7'>Error al cargar reservaciones.</td></tr>";
	        						}

	        						$pdo->close();
	        					?>
	        					</tbody>
	        				</table>
	        			</div>
	        		</div>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
	      </section>
	     
	    </div>
	  </div>
  
  	<?php include 'includes/footer.php'; ?>
  	<?php include 'includes/profile_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
    // Cambié .transact-btn por .transact para que coincida con el botón
    $(document).on('click', '.transact', function(e){
        e.preventDefault();
        var id = $(this).data('id');

        $.ajax({
            url: 'transaction.php', // corregí nombre archivo (antes 'transacion.php')
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                $('#date').text(data.date);
                $('#transid').text(data.transaction);
                $('#detail').html(data.list);
                $('#total').html(data.total);
                $('#transaction').modal('show');
            },
            error: function() {
                alert('Error al cargar los datos de la transacción');
            }
        });
    });
});
</script>
</body>
</html>
