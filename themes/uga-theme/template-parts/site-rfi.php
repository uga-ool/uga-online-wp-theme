<?php 
  $page_name = get_post_field( 'post_name', get_post());
  global $wp_query;
  $post = $wp_query->get_queried_object();
  $slug = $post->post_name;
  ?>

<div class=" util-background-light-gray util-pad-vert-xxl util-display-none@print">
    <div class="obj-content-width">
      <div id="request-form">
        <h2 class="cmp-heading-4 util-text-center"> Request Info</h2>
        <p>Slug: <?php echo($slug) ?></p>
        <p>Page Name: <?php echo($page_name) ?></p>
        <div class="obj-grid obj-grid--gap-xl@md util-margin-horiz-md">
          <div class="obj-grid__full obj-grid__half@md">
              
              <div class="cmp-form-field util-pad-top-xl">
                <div class="cmp-form-field--required">
                  <div class="util-position-relative">
                    <input id="first-name" class="cmp-form-field__input cmp-form-field__input--faux-placeholder" type="text" placeholder="First Name" required="">
                      
                      <label for="first-name" class="cmp-form-label cmp-form-label--faux-placeholder">
                        First Name
                      </label>
                  </div>
                </div>
              </div>
              
              <div class="cmp-form-field util-pad-top-xl">
                <div class="cmp-form-field--required">
                  <div class="util-position-relative">
                    <input id="last-name" class="cmp-form-field__input cmp-form-field__input--faux-placeholder" type="text" placeholder="Last Name" required="">
                      
                      <label for="last-name" class="cmp-form-label cmp-form-label--faux-placeholder">
                        Last Name
                      </label>
                  </div>
                </div>
              </div>
      <div class="cmp-form-select util-margin-vert-lg">  
        <label for="program-of-interest" class="cmp-form-label">
          Program of Interest
        </label>
      <div class="">
          <select id="program-of-interest" class="cmp-form-select__dropdown">
            <option value="1">Option 1</option><option value="2">Option 2</option><option value="3">Option 3</option><option value="4">Option 4</option><option value="">Option 5</option>
          </select>
        </div>
      </div>
      <div class="cmp-form-select util-margin-top-lg">  
        <label for="location" class="cmp-form-label">
          Location
        </label>
      <div class="">
          <select id="location" class="cmp-form-select__dropdown">
            <option value="1">United States</option><option value="2">Option 2</option><option value="3">Option 3</option><option value="4">Option 4</option><option value="5">Option 5</option>
          </select>
        </div>
      </div>
              
              <div class="cmp-form-field util-pad-top-xl">
                <div class="">
                  <div class="util-position-relative">
                    <input id="postal-code" class="cmp-form-field__input cmp-form-field__input--faux-placeholder" type="text" placeholder="Postal Code">
                      
                      <label for="postal-code" class="cmp-form-label cmp-form-label--faux-placeholder">
                        Postal Code
                      </label>
                  </div>
                </div>
              </div>
          </div>
          <div class="obj-grid__full obj-grid__half@md">
              
              <div class="cmp-form-field util-pad-top-xl">
                <div class="cmp-form-field--required">
                  <div class="util-position-relative">
                    <input id="email" class="cmp-form-field__input cmp-form-field__input--faux-placeholder" type="text" placeholder="Email Address" required="">
                      
                      <label for="email" class="cmp-form-label cmp-form-label--faux-placeholder">
                        Email Address
                      </label>
                  </div>
                </div>
              </div>
              
              <div class="cmp-form-field util-pad-top-xl">
                <div class="">
                  <div class="util-position-relative">
                    <input id="number" class="cmp-form-field__input cmp-form-field__input--faux-placeholder" type="text" placeholder="Phone Number">
                      
                      <label for="number" class="cmp-form-label cmp-form-label--faux-placeholder">
                        Phone Number
                      </label>
                  </div>
                </div>
              </div>
              
              <div class="cmp-form-field util-pad-top-xl">
                  <div class="">
                    
                    <label for="questions" class="cmp-form-label">
                      Questions/Comments
                    </label>
                  </div>
                <textarea id="questions" class="cmp-form-field__textarea"></textarea>
              </div>
          </div>
          <div class="obj-grid__full util-pad-top-xl util-margin-bottom-none@md">
            <p class="cmp-heading-6 util-margin-bottom-none">Captcha</p>
            <p class="util-margin-top-none">Let us know that you are not a robot to prevent automated spam submissions.</p>
            <div class="form-group">
            <div class="g-recaptcha" data-sitekey="your_key" data-theme="light"><div style="width: 304px; height: 78px;"><div><iframe title="reCAPTCHA" src="https://www.google.com/recaptcha/api2/anchor?ar=1&amp;k=your_key&amp;co=aHR0cHM6Ly9kZXNpZ24ub25saW5lLnVnYS5lZHU6NDQz&amp;hl=en&amp;v=TDBxTlSsKAUm3tSIa0fwIqNu&amp;theme=light&amp;size=normal&amp;cb=dp9mlhtumvya" width="304" height="78" role="presentation" name="a-xnzssikddsux" frameborder="0" scrolling="no" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation allow-modals allow-popups-to-escape-sandbox"></iframe></div><textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid rgb(193, 193, 193); margin: 10px 25px; padding: 0px; resize: none; display: none;"></textarea></div><iframe style="display: none;"></iframe></div>
            <style>
              @media (max-width: 795px){
                .g-recaptcha {transform:scale(0.77);transform-origin:0;transform:scale(0.77);transform-origin:0 0; }
              }
            </style>
            <div class="help-block with-errors"></div>
            </div>
          </div>
          <div class="obj-grid__full obj-grid__third@md">
            <button class="cmp-button
               cmp-button--full-width cmp-button--has-icon" type="button">
              Submit
            <svg class="cmp-button__icon cmp-button__icon--right">
                  <use xlink:href="#icon-arrow-right"></use>
                </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>