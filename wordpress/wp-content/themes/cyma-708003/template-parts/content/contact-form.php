<?php
/**
 * Working Contact Us form (CMS seed strips <form>/<input>).
 * When ?role= is present, shows the job-application variant.
 */
$role = isset( $_GET['role'] ) ? sanitize_text_field( wp_unslash( $_GET['role'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_apply = ( $role !== '' );
$arrow    = get_template_directory_uri() . '/assets/images/signupbtn.svg';

$contact_status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_success     = ( 'success' === $contact_status );
$is_error       = ( 'error' === $contact_status );

$interest_options = array(
	'Business Solutions'  => 'Business Solutions',
	'Technology Services' => 'Technology Services',
	'Job Opportunities'   => 'Job Opportunities',
	'Other'               => 'Other',
);

if ( $is_apply && ! isset( $interest_options[ $role ] ) ) {
	$interest_options = array( $role => $role ) + $interest_options;
}

$default_interest = $is_apply ? $role : '';
$message_value    = $is_apply
	? sprintf( 'I am applying for the %s role.', $role )
	: '';

$form_classes = 'form-block-3-copy w-form cyma-contact-form';
if ( $is_apply ) {
	$form_classes .= ' cyma-contact-form--apply';
}
if ( $is_success ) {
	$form_classes .= ' cyma-contact-form--success';
}
if ( $is_error ) {
	$form_classes .= ' cyma-contact-form--error';
}
?>
<div id="Business-Form---Contact-Us" class="<?php echo esc_attr( $form_classes ); ?>">
  <?php if ( ! $is_success ) : ?>
  <form id="cyma-contact-form" name="cyma-contact-form" method="post" class="form-3" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="cyma_contact_submit">
    <?php if ( $is_apply ) : ?>
      <input type="hidden" name="contact[role]" value="<?php echo esc_attr( $role ); ?>">
    <?php endif; ?>
    <?php wp_nonce_field( 'cyma_contact_submit', 'cyma_contact_nonce' ); ?>

    <div class="div-block-1080">
      <div class="div-block-1087">
        <label for="cyma-full-name" class="field-label">Full Name*</label>
        <input class="text-field w-input" maxlength="256" name="contact[full-name]" placeholder="Enter Your First Name" type="text" id="cyma-full-name" required>
      </div>
      <div class="div-block-1087">
        <label for="cyma-email" class="field-label">Email ID*</label>
        <input class="text-field w-input" maxlength="256" name="contact[email]" placeholder="Enter Your Email ID" type="email" id="cyma-email" required>
      </div>
    </div>

    <div class="div-block-1080">
      <div class="div-block-1087">
        <label for="cyma-mobile" class="field-label">Mobile Number*</label>
        <input class="text-field w-input" maxlength="256" name="contact[mobile-number]" placeholder="Enter Your Mobile Number" type="tel" id="cyma-mobile" required>
      </div>
      <div class="div-block-1087 div-block-1390">
        <label for="cyma-interest" class="field-label">I am interested in...</label>
        <select id="cyma-interest" name="contact[interested-in]" required class="select-field-2 select-field-3 w-select">
          <option value="">Select one...</option>
          <?php foreach ( $interest_options as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $default_interest, $value ); ?>><?php echo esc_html( $label ); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="div-block-1347">
      <label for="cyma-message" class="field-label">Send Us A Message</label>
      <textarea id="cyma-message" name="contact[message]" maxlength="5000" placeholder="Enter Your Text Here ......" class="text-field message w-input"><?php echo esc_textarea( $message_value ); ?></textarea>
    </div>

    <div class="div-block-1439 cyma-contact-submit-wrap">
      <div class="div-block-1384">
        <input type="submit" class="submit-button-2 w-button" value="<?php echo esc_attr( $is_apply ? 'Submit Application' : 'Submit' ); ?>">
        <img src="<?php echo esc_url( $arrow ); ?>" loading="lazy" alt="" class="image-208">
      </div>
    </div>
  </form>
  <?php endif; ?>
  <div class="success-message-3 w-form-done"<?php echo $is_success ? ' style="display:block"' : ''; ?> role="status"<?php echo $is_success ? ' aria-live="polite"' : ''; ?>>
    <div>Thank you! Your submission has been received!</div>
  </div>
  <div class="error-message-4 w-form-fail"<?php echo $is_error ? ' style="display:block"' : ''; ?> role="alert"<?php echo $is_error ? ' aria-live="assertive"' : ''; ?>>
    <div>Oops! Something went wrong while submitting the form.</div>
  </div>
</div>
