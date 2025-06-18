option_settings:
  aws:elasticbeanstalk:container:php:phpini:
    document_root: /public
    memory_limit: 512M
    zlib.output_compression: "Off"
    allow_url_fopen: "On"
    display_errors: "Off"
    max_execution_time: 60
    upload_max_filesize: 100M
    post_max_size: 100M
  aws:elasticbeanstalk:environment:proxy:staticfiles:
    /public: public 