<script id="progress-bar-section" type="text/x-handlebars-template">
<div class="user-profile-status full-width">
      <div class="user-profile-status-col user-profile-status-graph pull-right">
        <div class="onboarding-steps-status" data-percent="{{ data.progress }}" data-duration="2000" data-color=",#0D47A1;">
          You have completed <span id="steps-percentage">{{ data.progress }}%</span> of the onboarding. To invite your community please complete the other tasks:
        </div>
      </div>

      <div class="user-profile-status-mobile">
        <div class="user-profile-status-col onboarding-status-wrap">

        <div class="wc-step-col full-width onboarding-step admin-progress-icon">
        <div class="wc-step-done onboarding-step-tile {{#if data.space_admin}} onboarding-step-done {{/if}}" data-step="1">
        <p>1. Invite Admin</p>
        {{#if data.space_admin}}
                <span class="step-completed">
                <img src="{{ baseurl }}/images/v2-images/tick-icon-green.svg" alt="tick" /> Done!
                </span>
             {{else}}
                     <span class="step-uncompleted" data-step="2">
                          <img src="{{ baseurl }}/images/v2-images/add-icon-grey.svg" alt="tick" /> Complete
              </span>
               {{/if}}
            </div>
          </div>

          <div class="wc-step-col full-width onboarding-step ">
            <div class="wc-step-done onboarding-step-tile {{#if data.logo}} onboarding-step-done {{/if}}" data-step="2">
              <p>2. Add Logos</p>
              {{#if data.logo}}
              <span class="step-completed">
                <img src="{{ baseurl }}/images/v2-images/tick-icon-green.svg" alt="tick" /> Done!
              </span>
              {{else}}
              <span class="step-uncompleted" data-step="2">
              <img src="{{ baseurl }}/images/v2-images/add-icon-grey.svg" alt="tick" /> Complete
              </span>
              {{/if}}                        
            </div>
          </div>
          <div class="wc-step-col full-width onboarding-step">
          <div class="wc-step-done onboarding-step-tile {{#if data.background_image}}onboarding-step-done{{/if}}" data-step="3">
          <p>3. Add Banner</p>
            {{#if data.background_image}}
            <span class="step-completed">
              <img src="{{ baseurl }}/images/v2-images/tick-icon-green.svg" alt="tick" /> Done!
            </span>
            {{else}}
            <span class="step-uncompleted" data-step="3">
            <img src="{{ baseurl }}/images/v2-images/add-icon-grey.svg" alt="tick" /> Complete
            </span>
            {{/if}}
            </div>
          </div>
          <div class="wc-step-col full-width onboarding-step">
          <div class="wc-step-done onboarding-step-tile {{#if data.twitter_handles}}onboarding-step-done{{/if}}" data-step="4">
          <p>4. Pin Twitter</p>
            {{#if data.twitter_handles}}
            <span class="step-completed">
              <img src="{{ baseurl }}/images/v2-images/tick-icon-green.svg" alt="tick" /> Done!
            </span>
            {{else}}
            <span class="step-uncompleted" data-step="4">
            <img src="{{ baseurl }}/images/v2-images/add-icon-grey.svg" alt="tick" /> Complete
            </span>
            {{/if}}
            </div>
          </div>
          <div class="wc-step-col full-width onboarding-step">
          <div class="wc-step-done onboarding-step-tile {{#if data.domain}}onboarding-step-done{{/if}}" data-step="5">
          <p>5. Add Domain</p>
          {{#if data.domain}}
            <span class="step-completed">
              <img src="{{ baseurl }}/images/v2-images/tick-icon-green.svg" alt="tick" /> Done!
            </span>
            {{else}}
            <span class="step-uncompleted" data-step="5">
            <img src="{{ baseurl }}/images/v2-images/add-icon-grey.svg" alt="tick" /> Complete
            </span>
            {{/if}}
          </div>
          </div>
          <div class="wc-step-col full-width onboarding-step">
          <div class="wc-step-done onboarding-step-tile {{#if data.posts}}onboarding-step-done{{/if}}" data-step="6">
          <p>6. Add Posts</p>
            {{#if data.posts}}
            <span class="step-completed">
              <img src="{{ baseurl }}/images/v2-images/tick-icon-green.svg" alt="tick" /> Done!
            </span>
            {{else}}
            <span class="step-uncompleted" data-step="6">
                    <img src="{{ baseurl }}/images/v2-images/add-icon-grey.svg" alt="tick" /> Complete
            </span>
            {{/if}}
            </div>
          </div>
        </div>
      </div>

    </div> 
</script>