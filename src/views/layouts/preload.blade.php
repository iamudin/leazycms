@if(get_option('preload')  && get_option('preload')=='N')
<style type="text/css">
    #loading-spin {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255, 255, 255, .99);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .spinner-spin {
      position: relative;
      width: 100px;
      height: 100px;
    }

    .spinner-spin::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100px;
      height: 100px;
      border: 5px solid transparent;
      border-top-color: #ff5733; /* Warna pertama */
      border-right-color: #33ff57; /* Warna kedua */
      border-bottom-color: #3357ff; /* Warna ketiga */
      border-left-color: #f5a623; /* Warna keempat */
      border-radius: 50%;
      animation: spinspin 1s linear infinite;
    }

    .spinner-spin img {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%); /* Memastikan logo di tengah */
      width: 50px;
      height: 50px;
      object-fit: contain;
    }

    @keyframes spinspin {
      from {
        transform: rotate(0deg);
      }
      to {
        transform: rotate(360deg);
      }
    }
</style>


  <div id="loading-spin">
    <div class="spinner-spin">
      <img src="{{ get_option('icon') }}" alt="Icon Spinnner">
    </div>
  </div>
  <script>
    window.addEventListener("load", () => {
        setTimeout(() => {
            const loadingElement = document.getElementById("loading-spin");
            loadingElement.style.display = "none";
        }, 1200);
    });
  </script>
@endif
