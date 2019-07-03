<script id="progress-bar-section" type="text/x-handlebars-template">
<div class="user-profile-status full-width">
      <span class="tile-heading full-width">
        Admin set up
        <span class="admin-progress-icon">
          <a href="javascript::void();" data-step="2">
            <img src="{{ baseurl }}/images/ic_addadmin.svg" alt="tick" />
          </a>
        </span>
      </span>

      <div class="user-profile-status-col col-xs-6 col-sm-12 col-md-4 user-profile-status-graph pull-right">
        <div class="cdev" data-percent="{{ data.progress }}" data-duration="2000" data-color=",#0D47A1;">
          <p>complete</p>
        </div>
      </div>

      <div class="user-profile-status-mobile col-xs-6 col-sm-12 col-md-8">
        <div class="user-profile-status-col col-xs-12 col-sm-12 col-md-6 pull-left">
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.logo}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="3">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Logos</p>
          </div>
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.background_image}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="4">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Banner</p>
          </div>
          <div class="wc-step-col full-width">
            <span class="wc-step-done category_step {{#if data.category}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="5">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Categories</p>
          </div>
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.executive_summary}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="6">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Executive Summary</p>
          </div>
        </div>

        <div class="user-profile-status-col col-xs-12 col-sm-12 col-md-6 pull-left">
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.quick_links}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="7">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Quick Links</p>
          </div>
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.twitter_handles}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="8">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Twitter</p>
          </div>
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.domain}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="9">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>Domain Management</p>
          </div>
          <div class="wc-step-col full-width">
            <span class="wc-step-done {{#if data.posts}} {{else}} wc-step-uncomplete {{/if}} text-center" data-step="10">
              <img src="{{ baseurl }}/images/ic_tick.svg" alt="tick" />
            </span>
            <p>5 Posts</p>
          </div>
        </div>
      </div>

    </div> 
</script>