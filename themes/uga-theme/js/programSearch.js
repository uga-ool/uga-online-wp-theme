Vue.createApp({
  data() {
    return {
      url: 'http://uga-online-wp-dev.local/wp-json/wp/v2/programs',
      programs: [],
      filteredPrograms: [],
      imgUrls: [],
      imgLoaded: false,
      filters: {
        gre: null,
      },
      gre: null,

    }
  },
  created() {
    this.init();
  },
  methods: {
    async init() {
      await this.getData();

      for (p in this.programs) {
        await this.getImgUrl(this.programs[p].mediaUrl);
      }
      this.imgLoaded = true
      
    },

    getData() {
      return axios.get(this.url)
                  .then(response => {
                    for (i in response.data) {
                      var p = response.data[i];
                      this.programs.push({
                        title: p.title.rendered,
                        link: p.link,
                        slug: p.slug,
                        id: p.id,
                        mediaUrl: p._links['wp:attachment'][0]['href'],
                        gre: p.acf.gre_waived,
                      });
                    }
                    this.programs.sort((a, b) => (a.title > b.title) ? 1 : -1);
                    this.filteredPrograms = this.programs;
                  });
                  
    }, // End Get Data

    getImgUrl(imgUrl) {
      return axios.get(imgUrl)
          .then(response => {
            let data = response.data;

            if (data.length > 0) {
              this.imgUrls.push(data[0].media_details.sizes.medium.source_url)
            } else {
              this.imgUrls.push("https://via.placeholder.com/300x200")
            }

            

          });

    }, // End Get Img URL
  }, // End Methods

  watch: {
    gre(val, oldVal) {
      let bool = (val == "true")
      this.filteredPrograms = []
      for (let p in this.programs) {
        if (this.programs[p].gre == bool) {
          this.filteredPrograms.push(this.programs[p])
        }
      }
    }
  }, // End Watch
}).mount('#app')