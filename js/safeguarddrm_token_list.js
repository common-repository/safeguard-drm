var WPSGDRM_TOKEN_LIST = null;
(function ($) {
  WPSGDRM_TOKEN_LIST = {
    init: function () {
      let that = this;
      $(".btn-resend").on("click", function () {
        that.resend(this);
      });
    },

    resend: function (obj) {
      let that = this;

      let email = $(obj).attr("data-email");
      let token = $(obj).attr("data-token");

      $(obj).prop("disabled", true);
      that.notification_clear();
      $.ajax({
        method: "POST",
        url: ajaxurl + "?action=wpsgdrm_resend_token",
        data: {
          email: email,
          token: token,
          _wpnonce: wpsgdrm_token_list_data.nonce,
        },
      })
        .done(function (response) {
          if (!response.success && response.data.error) {
            that.notification_add(response.data.error, false);
          } else {
            that.notification_add(response.data.message, true);
          }
        })
        .always(function () {
          $(obj).prop("disabled", false);
        });
    },

    notification_clear: function () {
      $("#wpsgdrm-notifications").html("");
    },

    notification_add: function (message, success) {
      let html = `<div class="${
        success ? "updated" : "error"
      }"><p>${message}</strong></p></div>`;
      $("#wpsgdrm-notifications").html(html);
    },
  };

  $(document).ready(function () {
    WPSGDRM_TOKEN_LIST.init();
  });
})(jQuery);
