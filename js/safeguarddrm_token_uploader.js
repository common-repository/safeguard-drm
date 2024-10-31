//////////////////////////////////////////////////////////////
//This file handles the AJAX based token listing for editor shortcode.//
//////////////////////////////////////////////////////////////

jQuery(document).ready(function ($) {
  var file_name = null;
  var clicktrigger = false;

  $("#TB_ajaxContent").css({ width: "650px", height: "100%" });

  $(document).on("click", "#cancel", function () {
    $("#file_details").html("");
  });

  //jQuery('.sendtoeditor').live("click", function () {

  jQuery(document).on("click", "#wpsafeguarddrm_link", function () {
    if (clicktrigger) return;

    clicktrigger = true;

    jQuery(this).attr("disabled", true);

    var nname = jQuery(this).attr("data-value");

    wpsafeguarddrm_process_setting("sendeditor", "start");

    var file = "[safeguarddrm token='" + nname + "' ]";

    send_to_editor(file);

    wpsafeguarddrm_process_setting("sendeditor", "end");

    clicktrigger = true;

    return false;
  });

  $("#wpsafeguarddrm_div .ui-tabs-anchor").click(function () {
    var iid = $(this).attr("id");

    iid = iid.substring(0, iid.length - 3);

    $("#wpsafeguarddrm_div .ui-tabs-panel").hide();

    $("#" + iid).show();

    $(this)
      .parents(".ui-tabs-nav")
      .children(".ui-state-default")
      .removeClass("ui-state-active");

    $(this).parent().addClass("ui-state-active");
  });

  //----------------------------------------

  var wpsafeguarddrm_string_adjust = function (s, n) {
    var s_length = s.length;
    if (s_length <= n) return s;
    var c_n = Math.floor(n / 2);
    var e_n = s_length - n + 3 + c_n;
    s = s.substr(0, c_n) + "..." + s.substr(e_n);
    return s;
  };

  var pluginurl = $("#plugin-url").val();
  var plugindir = $("#plugin-dir").val();
  var upload_path = $("#upload-path").val();

  var prequeue = "";

  var wpsafeguarddrm_process_setting = function (frm, status) {
    if (status == "start") $("#wpsafeguarddrm_ajax_process").show();
    if (status == "end") $("#wpsafeguarddrm_ajax_process").hide();

    if (frm == "load") {
      if (status == "start") {
        $("#wpsafeguarddrm_message").html("");
        $("input:button").attr("disabled", true);
      }

      if (status == "end") {
        prequeue = "";
        $("#custom-queue").html("No file chosen");
        $("input:button").attr("disabled", false);
      }
    }
  };
});
