<?php 
    $video = get_field('feature_video');
?>

<div class="cmp-video util-margin-vert-lg">
  <script src="https://cdnapisec.kaltura.com/p/1727411/sp/172741100/embedIframeJs/uiconf_id/40170611/partner_id/1727411"></script>
  <div id="kaltura_player_1574196903" class="cmp-video__embed cmp-video__embed--dynamic" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">
    <span itemprop="class" content="cmp-video"></span>
    <span itemprop="name" content="<?php echo($video['video_name']); ?>"></span>
    <span itemprop="description" content="<?php echo($video['video_description']); ?>"></span>
    <span itemprop="duration" content="<?php echo($video['video_duration']); ?>"></span>
    <span itemprop="thumbnailUrl" content="https://cfvod.kaltura.com/p/1727411/sp/172741100/thumbnail/entry_id/<?php echo($video['kaltura_id']); ?>/version/100011"></span>
    <span itemprop="uploadDate" content="<?php echo($video['video_upload_date']); ?>"></span>
    <span itemprop="width" content="560"></span>
    <span itemprop="height" content="315"></span>
  </div>
  <script>
    kWidget.thumbEmbed({
      "targetId": "kaltura_player_1574196903",
      "wid": "_1727411",
      "uiconf_id": 40170611,
      "flashvars": {},
      "cache_st": 1574196903,
      "entry_id": "<?php echo($video['kaltura_id']); ?>",
    });
  </script>
</div>