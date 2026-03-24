<?php
require_once '../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "chat";
include_once(G5_PATH . '/head.php');


?>



<style>
    /* 상단 공간 및 하단 인풋 포함하여, 중앙부분 chat-wrapper 높이 지정 */
    .chat-wrapper {
      height: calc(100vh - 120px); /* 상단 헤더 높이에 맞춰 적절히 조정 */
    }
    /* 좌측 유저 목록 스크롤 */
    .user-list {
      height: 100%;
      overflow-y: auto;
    }
    /* 우측 채팅 내용 + 인풋을 세로로 배치하기 위해 flex 사용 */
    .chat-area {
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    /* 채팅 메시지 영역 스크롤 */
    .chat-content {
      flex: 1; /* 남은 영역 모두 사용 */
      overflow-y: auto;
      padding: 15px; /* 여백 */
    }
    /* 채팅 입력창은 항상 하단 고정 */
    .chat-input {
      border-top: 1px solid #ddd;
      padding: 10px;
    }
    /* 메시지 스타일 */
    .chat-message {
      max-width: 70%;
    }
    .chat-message .time {
      font-size: 0.8rem;
      color: #666;
    }
    /* 이름을 강조 표시하는 스타일 */
    .chat-message .user-name {
      font-weight: bold;
      margin-bottom: 3px;
    }

    /* 상대방 메시지 */
    .chat-message.other {
      display: flex;
      margin-bottom: 1rem;
    }
    .chat-message.other .msg-text {
      background-color: #f1f1f1;
      color: #333;
      padding: 10px;
      border-radius: 8px;
    }

    /* 내 메시지 */
    .chat-message.mine {
      display: flex;
      margin-bottom: 1rem;
      justify-content: flex-end;
      text-align: right;
    }
    .chat-message.mine .msg-text {
      background-color: #007bff;
      color: #fff;
      padding: 10px;
      border-radius: 8px;
    }

    /* 모바일에서 채팅목록 열기/닫기 버튼 여백 */
    .mobile-toggle-btn {
      margin-top: 10px;
    }

    /* .active 클래스가 적용된 목록 스타일 (Bootstrap4 기본 제공 + 원하는 스타일 추가 가능) */
    .list-group-item.active {
      background-color: #007bff;
      border-color: #007bff;
      color: #fff;
    }
</style>
</head>
<body>

<div class="container-fluid">
  <!-- 상단 영역 -->
  <div class="row mt-3">
    <div class="col text-right">
      <!-- 팀 선택 (여기서는 동작과 무관, 그대로 둠) -->
      <select class="custom-select d-inline-block w-auto">
        <option>1팀</option>
        <option>2팀</option>
        <option>3팀</option>
        <option>4팀</option>
      </select>
      <!-- 사용자 목록 (여기에 id 추가해서 선택값 가져오기) -->
      <select id="userSelect" class="custom-select d-inline-block w-auto">
        <!-- 랜덤 예시 -->
        <option>홍길동</option>
        <option>김길동</option>
        <option>이몽룡</option>
        <option>박길동</option>
        <option>최길동</option>
      </select>
      <!-- 대화하기 버튼 -->
      <button class="btn btn-primary btn-chat-start">대화하기</button>
    </div>
  </div>

  <!-- 모바일 전용: 채팅목록 열기/닫기 버튼 -->
  <div class="row d-md-none">
    <div class="col text-right mobile-toggle-btn">
      <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#mobileUserList" aria-expanded="false" aria-controls="mobileUserList">
        채팅목록 열기/닫기
      </button>
    </div>
  </div>

  <!-- 중앙 영역 -->
  <div class="row mt-3 chat-wrapper">
    <!-- ===== PC: 좌측 유저 목록 ===== -->
    <div class="col-md-4 border user-list p-0 d-none d-md-block">
      <div class="list-group" id="pcUserList">
        <!-- 예시 유저 -->
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center user-item">
          <img src="https://via.placeholder.com/50" alt="홍길동" class="rounded-circle mr-3" style="width:50px; height:50px;">
          <div>
            <strong>홍길동</strong>
          </div>
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center user-item">
          <img src="https://via.placeholder.com/50" alt="김길동" class="rounded-circle mr-3" style="width:50px; height:50px;">
          <div>
            <strong>김길동</strong>
          </div>
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center user-item">
          <img src="https://via.placeholder.com/50" alt="이몽룡" class="rounded-circle mr-3" style="width:50px; height:50px;">
          <div>
            <strong>이몽룡</strong>
          </div>
        </a>
      </div>
    </div>

    <!-- ===== Mobile: 좌측 유저 목록(접었다 펼침) ===== -->
    <div class="col-12 d-md-none collapse user-list p-0" id="mobileUserList">
      <div class="list-group" id="mobileListGroup">
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center user-item">
          <img src="https://via.placeholder.com/50" alt="홍길동" class="rounded-circle mr-3" style="width:50px; height:50px;">
          <div>
            <strong>홍길동</strong>
          </div>
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center user-item">
          <img src="https://via.placeholder.com/50" alt="김길동" class="rounded-circle mr-3" style="width:50px; height:50px;">
          <div>
            <strong>김길동</strong>
          </div>
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center user-item">
          <img src="https://via.placeholder.com/50" alt="이몽룡" class="rounded-circle mr-3" style="width:50px; height:50px;">
          <div>
            <strong>이몽룡</strong>
          </div>
        </a>
      </div>
    </div>

    <!-- 우측(or 전체) 채팅 영역 -->
    <!-- PC에서는 col-md-8, Mobile에서는 전체 col-12 -->
    <div class="col-md-8 col-12 border chat-area">
      <!-- 채팅 내용 영역 -->
      <div class="chat-content">
        <!-- 디폴트 예시 메시지 -->
        <div class="chat-message other">
          <img src="https://via.placeholder.com/50" alt="상대방" class="rounded-circle mr-2" style="width:50px; height:50px;">
          <div>
            <div class="user-name">홍길동</div>
            <div class="msg-text">안녕하세요? 잘 지내셨나요?</div>
            <div class="time text-right mt-1">10:00</div>
          </div>
        </div>
        <div class="chat-message mine">
          <div>
            <div class="user-name">나</div>
            <div class="msg-text">네, 반갑습니다!</div>
            <div class="time text-right mt-1">10:01</div>
          </div>
          <img src="https://via.placeholder.com/50" alt="나" class="rounded-circle ml-2" style="width:50px; height:50px;">
        </div>
      </div>
      <!-- 채팅 입력창 영역 -->
      <div class="chat-input">
        <div class="input-group">
          <input type="text" class="form-control" placeholder="메시지를 입력하세요...">
          <div class="input-group-append">
            <button class="btn btn-primary">전송</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- jQuery, Bootstrap4 JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  // ----------------------
  // 1) 기존 user-item 클릭 로직 (홍길동, 김길동, 이몽룡)
  // ----------------------
  $(document).ready(function() {
    $('.user-item').on('click', function(e) {
      e.preventDefault();

      // 1) 모든 user-item에서 active 제거
      $('.user-item').removeClass('active');
      // 2) 현재 클릭한 user-item에 active 추가
      $(this).addClass('active');
      // 3) 모바일에서는 목록 자동 닫기
      $('#mobileUserList').collapse('hide');

      // 클릭된 사용자의 이름 가져오기
      var clickedUserName = $(this).find('strong').text().trim();

      // 해당 사용자에 맞게 채팅 예시 하드코딩
      var chatHtml = '';

      if(clickedUserName === '김길동') {
        chatHtml = `
          <div class="chat-message other">
            <img src="https://via.placeholder.com/50" alt="김길동" class="rounded-circle mr-2" style="width:50px; height:50px;">
            <div>
              <div class="user-name">김길동</div>
              <div class="msg-text">안녕하세요, 김길동입니다. 잘 지내셨나요?</div>
              <div class="time text-right mt-1">09:30</div>
            </div>
          </div>
          <div class="chat-message mine">
            <div>
              <div class="user-name">나</div>
              <div class="msg-text">안녕하세요, 오랜만이네요!</div>
              <div class="time text-right mt-1">09:31</div>
            </div>
            <img src="https://via.placeholder.com/50" alt="나" class="rounded-circle ml-2" style="width:50px; height:50px;">
          </div>
        `;
      } 
      else if(clickedUserName === '이몽룡') {
        chatHtml = `
          <div class="chat-message other">
            <img src="https://via.placeholder.com/50" alt="이몽룡" class="rounded-circle mr-2" style="width:50px; height:50px;">
            <div>
              <div class="user-name">이몽룡</div>
              <div class="msg-text">안녕하세요, 이몽룡입니다. 잘 지내셨나요?</div>
              <div class="time text-right mt-1">10:10</div>
            </div>
          </div>
          <div class="chat-message mine">
            <div>
              <div class="user-name">나</div>
              <div class="msg-text">반갑습니다! 요즘 어떻게 지내세요?</div>
              <div class="time text-right mt-1">10:12</div>
            </div>
            <img src="https://via.placeholder.com/50" alt="나" class="rounded-circle ml-2" style="width:50px; height:50px;">
          </div>
        `;
      } 
      else if(clickedUserName === '홍길동') {
        chatHtml = `
          <div class="chat-message other">
            <img src="https://via.placeholder.com/50" alt="홍길동" class="rounded-circle mr-2" style="width:50px; height:50px;">
            <div>
              <div class="user-name">홍길동</div>
              <div class="msg-text">안녕하세요, 홍길동입니다. 잘 지내시죠?</div>
              <div class="time text-right mt-1">08:00</div>
            </div>
          </div>
          <div class="chat-message mine">
            <div>
              <div class="user-name">나</div>
              <div class="msg-text">네, 오랜만이에요!</div>
              <div class="time text-right mt-1">08:02</div>
            </div>
            <img src="https://via.placeholder.com/50" alt="나" class="rounded-circle ml-2" style="width:50px; height:50px;">
          </div>
        `;
      }
      else {
        // 기본값 or 다른 유저
        chatHtml = `
          <div class="chat-message other">
            <img src="https://via.placeholder.com/50" alt="상대방" class="rounded-circle mr-2" style="width:50px; height:50px;">
            <div>
              <div class="user-name">${clickedUserName}</div>
              <div class="msg-text">안녕하세요, ${clickedUserName}입니다!</div>
              <div class="time text-right mt-1">11:00</div>
            </div>
          </div>
          <div class="chat-message mine">
            <div>
              <div class="user-name">나</div>
              <div class="msg-text">반갑습니다!</div>
              <div class="time text-right mt-1">11:01</div>
            </div>
            <img src="https://via.placeholder.com/50" alt="나" class="rounded-circle ml-2" style="width:50px; height:50px;">
          </div>
        `;
      }

      // 채팅 내용 영역에 교체
      $('.chat-content').html(chatHtml);
    });


    // ----------------------
    // 2) "대화하기" 버튼 클릭 시 
    //    "최길동"만 특별히 처리 (채팅 내용이 없는 상태를 가정)
    // ----------------------
    $('.btn-chat-start').on('click', function() {
      // 상단 사용자 select에서 선택된 값 (id="userSelect")
      var selectedUser = $('#userSelect').val().trim();

      // 만약 "최길동"이라면 -> "채팅 내역이 없습니다" 같은 내용 출력
      if (selectedUser === '최길동') {
        var chatHtml = `
          <div class="chat-message other">
            <img src="https://via.placeholder.com/50" alt="최길동" class="rounded-circle mr-2" style="width:50px; height:50px;">
            <div>
              <div class="user-name">최길동</div>
              <div class="msg-text">아직 저장된 채팅 내용이 없습니다.</div>
              <div class="time text-right mt-1">00:00</div>
            </div>
          </div>
        `;
        $('.chat-content').html(chatHtml);
      } else {
        // 그 외 유저 클릭 시에는 아무 동작 안 하거나
        // "기본 안내" 등을 띄울 수도 있음(선택)
        alert(selectedUser + '님은 이미 목록 클릭으로 확인해주세요!');
      }
    });
  });
</script>


<?php
include_once(G5_PATH . '/tail.php');
?>
