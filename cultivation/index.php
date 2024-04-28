<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="MobileOptimized" content="176"/>
    <meta name="HandheldFriendly" content="True"/>
    <meta name="robots" content="noindex,nofollow"/>
    <title></title>
    <script src="https://telegram.org/js/telegram-web-app.js?1"></script>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/@tonconnect/ui@latest/dist/tonconnect-ui.min.js"></script>

    <script src="script/script.js"></script>

    <style>
        .popup {
          display: none;
          position: fixed;
          top: 58%;
          left: 50%;
          transform: translate(-50%, -50%);
          background-color: white;
          padding: 20px;
          border: 1px solid #ccc;
          border-radius: 5px;
          z-index: 9999;
          width:90%;
          height:75%;
        }

        .msgPopup {
          display: none;
          position: fixed;
          top: 20%;
          left: 50%;
          transform: translate(-50%, -50%);
          background-color: white;
          padding: 20px;
          border: 1px solid #ccc;
          border-radius: 5px;
          z-index: 99999;
          width:35%;
          height:10%;
        }
  </style>
</head>
<body>
<div class="container">
    <div id="ton-connect" style="position: absolute; right: 10%; display: none;"></div>
    <div id="btn_connectWallet" style="display: start;">
        <!-- <p style="color:#fff">Sẽ tốt hơn nếu bạn chơi trên phiên bản telegram web</p> -->
        <button onclick="start()" >Bắt đầu</button>
    </div>

    <div class="mining-container">
        <div id="resource" >
            <div id="tele_user"></div>
            <div id="address" >
                Wallet: <span id="wallet">Not connect</span>
                <button onclick="connectToWallet()" id="connect_wallet_button">Connect</button>
            </div>
            <div style="padding: 5px;">
                <span id="ore_quantity"></span> <img src="img/ore.png" id="ore_icon">
                
                <button onclick="hesoNhan()" id="hesoNhan" data-value="1">x1</button>
                <button onclick="sendTon()" id="btn_sendTon" style="">Nạp 1000<img src="img/ore.png" class="symbol"> = 0.1<img src="img/ton.png" class="symbol"/></button>
            </div>
        </div>

        <div class="image-container">
            <img src="img/mine3.gif" alt="Mining GIF" class="mining-gif">
            <div id="oreCount">+1</div>
        </div>
        

        <div class="button-container">   
            <!-- Nút để hiển thị popup danh sách học viên -->
            <button onclick="showStudentListPopup()"><img src="img/5.png" ></button>
            <!-- Nút để hiển thị popup tuyển mộ học viên -->
            <button onclick="showRecruitmentPopup()"><img src="img/kinhlup.png" ></button>
            <button onclick="showCombatPopup()"><img src="img/map.png" ></button>
            <button onclick="showBagPopup()"><img src="img/tuido.png" ></button>
            <button onclick="showMedicinePopup('Đan dược')"><img src="img/noithuoc.jpg" ></button>
            <button onclick="showMedicinePopup('Trang bị')"><img src="img/de.png" ></button>
            <button onclick="showMedicinePopup('Minh văn')"><img src="img/cove.png" ></button>
            <button onclick="showMedicinePopup('Trận pháp')"><img src="img/tranphap1.png" ></button>
            <button onclick="showShopPopup()"><img src="img/shop.png" ></button>
            <button onclick="showMarketPopup()"><img src="img/trade.png" ></button>
        </div>
        
        <p style="position:absolute; bottom: 0%; left: 5%; font-size:12px">Mẹo: Bạn có thể kết nối với Tonkeeper trên điện thoại hoặc Wallet in Telegram nếu bạn chơi game trên Telegrame web</p>
    </div>
</div>

<!-- Popup danh cảnh báo -->
<div id="msgPopup" class="msgPopup">
    <div class="popup-content">
        <div id="msg"></div>
        <button onclick="closeMsgPopup()" class="closePopupBtn">Đóng</button>
    </div>
</div>

<!-- Popup danh sách học viên -->
<div id="studentListPopup" class="popup">
    <div class="popup-content">
        <h2>Danh sách học viên</h2>
        <p >Tổng chiến lực:<b id="total_power"></b></p>
        <div id="student_list"></div>
        <!-- Nội dung danh sách học viên sẽ được thêm vào đây -->
        <button onclick="closePopup()" class="closePopupBtn">Đóng</button>
    </div>
</div>

<!-- Popup tuyển mộ học viên -->
<div id="equipmentPopup" class="popup sellPopup">
    <div class="popup-content">
        <h2>Chọn trang bị để mang cho học viên</h2>
        <div id="equipment_item"></div>
        <button onclick="closeEquipmentPopup()" class="closePopupBtn">Đóng</button>
    </div>
</div>


<!-- Popup tuyển mộ học viên -->
<div id="recruitmentPopup" class="popup">
    <div class="popup-content">
        <h2>Tuyển mộ học viên</h2>
        <img src="" id="recuit_student_img" style="width:40%">
        <div id="recuit_student">
            <b id="recuit_student_name"></b><br>
            <p>Cấp độ 1</p>
            <div id="recuit_student_quality"></div>
            <div id="recuit_student_innate"></div>
            <p>(Cứ 10 cấp thiên phú sẽ tăng hiệu quả)</p>
            <button id="recuit_student_btn" onclick="recuitStudentWithCost();">Tuyển mộ</button>
            <button id="recuit_student_renew_btn" onclick="recuitStudentRenew()">Làm mới: 100 Ore</button>

            <button onclick="closePopup()" class="closePopupBtn">Đóng</button>
        </div>
        <!-- Nội dung tuyển mộ học viên sẽ được thêm vào đây -->
    </div>
</div>

<!-- Popup tuyển mộ học viên -->
<div id="combatPopup" class="popup">
    <div class="popup-content">
        <h2>Lịch luyện</h2>
        <p>Chọn bản đồ lịch luyện</p>
        <div id="map_list">
        </div>
        <button onclick="closeCombatPopup()" class="closePopupBtn" id="combatCloseBtn">Đóng</button>
    </div>
</div>

<!-- Popup tuyển mộ học viên -->
<div id="bagPopup" class="popup">
    <div class="popup-content">
        <h2>Túi đồ</h2>
        <div id="user_bag">
        </div>
        <button onclick="closePopup()" class="closePopupBtn" id="">Đóng</button>
    </div>
</div>

<!-- Popup tuyển mộ học viên -->
<div id="sellPopup" class="popup sellPopup">
    <div class="popup-content">
        <h2 id="sellPopupTitle">Bán</h2>
        <div id="sell_item">
        </div>
        <button onclick="closeSellPopup()" class="closePopupBtn">Đóng</button>
    </div>
</div>

<div id="shopPopup" class="popup">
    <div class="popup-content">
        <h2>Shop</h2>
        <div id="shop_list">
        </div>
        <button onclick="closePopup()" class="closePopupBtn" id="">Đóng</button>
    </div>
</div>

<div id="buyPopup" class="popup sellPopup">
    <div class="popup-content">
        <h2>Mua</h2>
        <div id="buy_item">
        </div>
        <button onclick="closeBuyPopup()" class="closePopupBtn">Đóng</button>
    </div>
</div>

<!-- Popup tuyển mộ học viên -->
<div id="medicinePopup" class="popup">
    <div class="popup-content">
        <h2 id="craff_title">Luyện đan</h2>
        <p id="craff_alt">Chọn đan dược muốn luyện</p>
        <div id="medicine_list">
        </div>
        <button onclick="closeMedicinePopup()" class="closePopupBtn" >Đóng</button>
    </div>
</div>

<!-- Popup tuyển mộ học viên -->
<div id="marketPopup" class="popup">
    <div class="popup-content">
        <h2 id="craff_title">Market Place</h2>
        <div>
            <button onclick="showListMarket('buy')"  id="showBuyMarketBtn">Mua</button>
            <button onclick="showListMarket('sell')" id="showSellMarketBtn">Đang bán</button>
        </div>
        <div id="market_list" data-type="sell" style="margin: 20px 0px;border: 1px solid black;">
        </div>
        <button onclick="closePopup()" class="closePopupBtn" >Đóng</button>
    </div>
</div>

</body>
</html>