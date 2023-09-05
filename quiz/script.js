//logs
function add_log(title, text, user, destination, data){
  $.ajax({
      type: 'POST',
      url: 'log.php',
      data: {title: title, text: text, user: user, destination: destination, data: data},
  });
}
//notify
function notifyshow(message, type) {
  $.notify(message, {
      position: "top right",
      className: type,
      autoHideDelay: 5000,
      icon: null,
  });
  $(".notifyjs-wrapper").addClass("custom-color");
}
//user status
window.addEventListener('online', () => {
  changeStatus("online");
  notifyshow(lang_text['quiz']['online'], '');
});
window.addEventListener('offline', () => {
  notifyshow(lang_text['quiz']['offline'], '');
});
function changeStatus(status) {
  $.ajax({
    type: "POST",
    url: "db/update_status.php",
    data: {status: status},
    async: false,
    error: function(xhr, status, error) {
      add_log("index: change status", "AJAX: "+error, "script.js", "./logs/", xhr.status);
    }
  });
}
$(document).ready(function() {
  setInterval(function() {
    changeStatus("online");
  }, 10000);
});
//logout
$(document).ready(function() {
  $('.logout').click(function() {
    changeStatus("offline");
    window.location.href = "logout.php";
  });
});
//resize menu
document.documentElement.style.setProperty('--window-height', `${window.innerHeight}px`);
window.addEventListener('resize', () => {
  document.documentElement.style.setProperty('--window-height', `${window.innerHeight}px`);
});
const save_score_table = document.getElementById('menu');
const close_save_button_IMG = document.querySelector('#close_save img');
const body = document.body;
//save score table style
function closeSaveScore() {
  if(save_score_table.classList.contains('hide')) {
    save_score_table.style.top = '0';
    body.classList.add('no-scroll');
    save_score_table.classList.add('show');
    save_score_table.classList.remove('hide');
    close_save_button_IMG.style.transform = 'rotate(-90deg)';
  } else {
    save_score_table.style.top = '-95%';
    body.classList.remove('no-scroll');
    close_save_button_IMG.style.transform = 'rotate(90deg)';
    save_score_table.classList.add('hide');
    save_score_table.classList.remove('show');
  }
}
//settings
$(document).ready(function() {
  $('#show_settings_button').click(function() {
    $.ajax({
      type: "POST",
      url: "db/show_settings.php",
      success: function(response) {
        $('.settings_menu').fadeIn(500);
        $('.settings_menu').html(response);
        $('.close_score_settings').click(function() {
          $('.settings_menu').fadeOut(500);
        });
        $('.settings_menu_main_background').click(function() {
          $('.close_score_settings').click();
        });
      },
      error: function(xhr, status, error) {
          add_log("index: settings", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
});
//darkmode
$(document).ready(function() {
  function darkmodeCheck(){
    if (sessionStorage.getItem("dark") == 1) {
      document.body.classList.remove("light-mode");
      document.body.classList.add("dark-mode");
      $('.darkmode').html("<i class=\"fa-regular fa-lightbulb\"></i>");
    }else{
      document.body.classList.remove("dark-mode");
      document.body.classList.add("light-mode");
      $('.darkmode').html("<i class=\"fa-solid fa-lightbulb\"></i>");
    }
  }
  $('.darkmode').click(function() {
    $.ajax({
      type: "POST",
      url: "db/darkmode.php",
      success: function() {
        sessionStorage.setItem("dark", sessionStorage.getItem("dark") == 1 ? 0 : 1);
        darkmodeCheck();
      },
      error: function(xhr, status, error) {
          add_log("index: darkmode", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
  darkmodeCheck();
});
//show score
$(document).ready(function() {
  $('#show_score_button').click(function() {
    $('#show_table').css({"-webkit-transform":"translateX(0%)"});
    $.ajax({
      type: "POST",
      url: "db/show_score.php",
      success: function(response) {
        $('#show_table').html(response);
        $('.close_score_show').click(function() {
          $('#show_table').css({"-webkit-transform":"translateX(-110%)"});
        });
      },
      error: function(xhr, status, error) {
          add_log("index: show score", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
});
//show quiz
$(document).ready(function() {
  $('.select_subject_submit').click(function(e) {
    var number_of_questions = Math.round(document.querySelector('.select_subject_numberOfQuestions_free input').value);
    var subject = document.querySelector(".select_subject_wrapper").querySelector('.select_subject_select_btn').firstElementChild.innerText;
    document.querySelector('.select_subject_submit span').innerText = lang_text['quiz']['select_quiz']['load_quiz'];
    if(number_of_questions==1){
      var one_to_one = true;
    }
    $.ajax({
      type: "POST",
      data: {subject: subject, number_of_questions: number_of_questions},
      url: "db/show_quiz.php",
      success: function(response) {
        $('#quiz_load').html(response);
        document.querySelector('.select_subject_submit span').innerText = lang_text['quiz']['select_quiz']['start_quiz'];
        window.scrollTo(0, 0);
        document.querySelector('.quiz_score').style.display = "flex";
        document.getElementById('score_quiz').innerHTML = "0.00";
        $('#save_score').removeAttr('disabled');
        if(document.getElementById('score_quiz_all') !== null){
          if(question_count>0){
            if(one_to_one){
              document.getElementById('score_quiz_all').innerHTML = 1.00;
            }else{
              document.getElementById('score_quiz_all').innerHTML = question_count;
            }
          }else{
            document.getElementById('score_quiz_all').innerHTML = 0.00;
          }
        }
        document.getElementById('menu').classList.add('show');
        save_score_table.style.top = '-95%';
        body.classList.remove('no-scroll');
        close_save_button_IMG.style.transform = 'rotate(90deg)';
        save_score_table.classList.add('hide');
        save_score_table.classList.remove('show');
        $('#close_save').attr('onclick', 'closeSaveScore()');
        var last_question_id = document.getElementById('quiz_results').getAttribute('data-last_id');
        if(document.getElementById('quiz_results')!==null) {
          const quiz_results = document.getElementById('quiz_results');
          var final_score = 0;
          if(one_to_one){
            var count_one_to_one = 1;
            var correct = 0;
          }
          quiz_results.addEventListener('click', (event) => {
            if(!event.target.classList.contains('radio')) {
              return;
            }
            if(event.target.parentElement.classList.contains('report_question_active')) {
              return;
            }
            const selected_answer = event.target;
            const subject = selected_answer.getAttribute('subject');
            const question_number = selected_answer.getAttribute('name');
            const question_number_short = question_number.substring(9);
            var correct_answer = "";
            $.ajax({
              url: 'db/quiz_correct.php',
              type: 'POST',
              data: {question_number: question_number_short, subject: subject},
              success: function(data){
                correct_answer = data;
                if(correct_answer == "valid"){
                  var xhr = new XMLHttpRequest();
                  xhr.open('POST', 'check_answer.php', true);
                  xhr.onreadystatechange = function() {
                      if (xhr.readyState === 4 && xhr.status === 200){
                        window.location.reload();
                      }
                  };
                  xhr.send();
                  return;
                }
                var max_checked = correct_answer.length;
                const all_answers = quiz_results.querySelectorAll(`[name="${question_number}"]`);
                let checked_count = 0;
                let in_correct = 0;
                all_answers.forEach(input => {
                  if (input.classList.contains('checked')) {
                    checked_count++;
                  }
                  if(input.classList.contains('incorrect')) {
                    checked_count = max_checked;
                  }
                });
                if (checked_count === max_checked || checked_count >= max_checked) {
                  return;
                } else {
                  selected_answer.classList.add('checked');
                  all_answers.forEach(answer => {
                    if (answer.classList.contains('checked')) {
                      const matches = correct_answer.match(answer.getAttribute('value'));
                      if (matches) {
                        if(!answer.classList.contains('correct')){
                          answer.classList.add('correct');
                          final_score += 1.0000000000/max_checked;
                          if(one_to_one){
                            correct += 1.0000000000/max_checked;
                          }
                        }
                      } else {
                        if(!answer.classList.contains('incorrect')){
                          answer.classList.add('incorrect');
                          in_correct++;
                        }
                      }
                    }
                  });
                  let check_analytic = document.querySelectorAll(`[name="question-${question_number_short}"].checked`).length;
                  let check_analytic_answer = "";
                  document.querySelectorAll(`[name="question-${question_number_short}"].checked`).forEach(answer => {
                    check_analytic_answer += answer.getAttribute('value');
                  });
                  let check_analytic_answer_number =selected_answer.parentElement.querySelector('.number_of_all_questions').innerHTML;
                  if(!one_to_one){
                    check_analytic_answer_number = check_analytic_answer_number.substring(0, check_analytic_answer_number.indexOf('/'));
                    check_analytic_answer_number = check_analytic_answer_number.replace(/^\s+|\s+$/gm,'');
                  }
                  let correct_analytic = document.querySelectorAll(`[name="question-${question_number_short}"].correct`).length;
                  let incorrect_analytic = document.querySelectorAll(`[name="question-${question_number_short}"].incorrect`).length;
                  var analytic = [
                    ["subject", subject],
                    ["id_question", question_number_short],
                    ["checked",check_analytic],
                    ["correct",correct_analytic],
                    ["incorrect", incorrect_analytic],
                    ["maxchecked", max_checked]];
                  var selected_answers = [
                    ["id", check_analytic_answer_number],
                    ["subject", subject],
                    ["id_question", question_number_short],
                    ["answers",check_analytic_answer],
                    ["correct_answers",correct_answer]];
                  if(in_correct == 1){
                    all_answers.forEach(answer => {
                      const matches_incorrect = correct_answer.match(answer.getAttribute('value'));
                      if (matches_incorrect) {
                        answer.classList.add('correct');
                      }
                    });
                  }
                  if(max_checked == document.querySelectorAll(`[name="question-${question_number_short}"].correct`).length){
                    $.ajax({
                      type: "POST",
                      url: "db/analytic_change.php",
                      data: {analytic: analytic},
                      success: function() {
                        $.ajax({
                          type: "POST",
                          url: "db/analytic_change.php",
                          data: {selected_answers: selected_answers},
                          success: function() {
                            if(question_number_short == last_question_id){
                              if(max_checked === checked_count + 1 || selected_answer.classList.contains('incorrect')){
                                $('#save_score').click();
                                closeSaveScore();
                                clearInterval(timer);
                              }
                            }
                          },
                          error: function(xhr, status, error) {
                              add_log("index: score change", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                              notifyshow(status+" ("+xhr.status+"): "+error, '');
                          }
                        });
                      },
                      error: function(xhr, status, error) {
                          add_log("index: analytic change", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                          notifyshow(status+" ("+xhr.status+"): "+error, '');
                      }
                    });
                  }
                  if ((one_to_one && in_correct == 1) || (one_to_one && correct.toFixed(2) == 1)) {
                    correct = 0;
                    let next_question = count_one_to_one + 1;
                    if(document.querySelector(".background_question:nth-child("+next_question+")")){
                      let questions = document.querySelector(".background_question:nth-child("+next_question+")");
                      questions.classList.remove('hidden_question');
                      document.getElementById('score_quiz_all').innerHTML = count_one_to_one;
                      count_one_to_one++;
                    }else{
                      document.getElementById('score_quiz_all').innerHTML = next_question - 1;
                    }
                  }
                }
                document.getElementById('score_quiz').innerHTML = final_score.toFixed(2);
              },
              error: function(xhr, status, error) {
                  add_log("index: quiz correct", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                  notifyshow(status+" ("+xhr.status+"): "+error, '');
              }
            });
          });
        }
      },
      error: function(xhr, status, error) {
          add_log("index: show quiz", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
});
//save score
$('#save_score').click(function() {
  $('#close_save').removeAttr('onclick');
  if($('#save_score').attr('disabled') == 'disabled') {
    return false;
  }
  $('#save_score').attr('disabled', 'disabled');
  $('#save_score').html('<i class=\"fa-regular fa-floppy-disk fa-beat-fade\" style=\"--fa-beat-fade-opacity: 0.67; --fa-beat-fade-scale: 1.075;\"></i>');
  if(question_count != 0) {
    clearInterval(timer);
    $.ajax({
      type: "POST",
      url: "db/save_score.php",
      data: {score: $('#score_quiz').text(), question_count: $('#score_quiz_all').text(),subject: subject, quiz_start: quiz_start},
      success: function(response) {
        notifyshow(response, '');
        $.ajax({
          type: "POST",
          url: "db/notifications.php",
          data: {id: "bellreload"},
          success: function(response) {
            $('.notification_bell').html(response);
            menu.classList.remove('show');
            menu.classList.add('hide');
            $('.notification_background').css('display', 'none');
            $('#save_score').html("<i class='fa-regular fa-floppy-disk'></i>");
          },
          error: function(xhr, status, error) {
              add_log("index: reload notification bell", "AJAX: "+error, "script.js", "./logs/", xhr.status);
              notifyshow(status+" ("+xhr.status+"): "+error, '');
          }
        });
      },
      error: function(xhr, status, error) {
          add_log("index: save score", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  }
});
//notifications
var notification = document.querySelectorAll('.notification');
var notification_array = Array.prototype.slice.call(notification,0);
notification_array.forEach(function(el){
  var button = el.querySelector('a[data-toggle="notification"]'),
      menu = el.querySelector('.notification_menu');
  $('.notification_background').click(function() {
    $.ajax({
      type: "POST",
      url: "db/notifications.php",
      data: {id: "bellreload"},
      success: function(response) {
        $('.notification_bell').html(response);
        menu.classList.remove('show');
        menu.classList.add('hide');
        $('.notification_background').css('display', 'none');
      },
      error: function(xhr, status, error) {
          add_log("index: close notification", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
  button.onclick = function(event) {
    if(!menu.hasClass('show')) {
      var arrow = button.querySelector('i.fa-bell')
      if(arrow.hasClass('bell_animation')) {
        arrow.classList.remove('bell_animation');
      }
      $.ajax({
        type: "POST",
        url: "db/notifications.php",
        success: function(response) {
          $('.notification_menu').html(response);
          menu.classList.add('show');
          menu.classList.remove('hide');
          var flag_elements = document.querySelectorAll('.fa-flag');
          var all_notifications = document.querySelector('.clear_all_notification');
          if(all_notifications){
            all_notifications.onclick = function() {
              $.ajax({
                type: "POST",
                url: "db/notifications.php",
                data: {id: 'all'},
                success: function() {
                  $.ajax({
                    type: "POST",
                    url: "db/notifications.php",
                    data: {id: "bellreload"},
                    success: function(response) {
                      $('.notification_bell').html(response);
                      menu.classList.remove('show');
                      menu.classList.add('hide');
                      $('.notification_background').css('display', 'none');
                    },
                    error: function(xhr, status, error) {
                        add_log("index: close notification after remove all", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                  });
                },
                error: function(xhr, status, error) {
                    add_log("index: delete all notifications", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
              });
            }
          }
          flag_elements.forEach((flag) => {
            flag.addEventListener('click', () => {
              var flagid = flag.getAttribute('data-id');
              flag.classList.remove('fa-solid');
              $.ajax({
                type: "POST",
                url: "db/notifications.php",
                data: {id: flagid},
                success: function() {
                  flag.classList.add('fa-regular');
                },
                error: function(xhr, status, error) {
                    add_log("index: remove one notification", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
              });
            });
          });
        },
        error: function(xhr, status, error) {
            add_log("index: load notification", "AJAX: "+error, "script.js", "./logs/", xhr.status);
            notifyshow(status+" ("+xhr.status+"): "+error, '');
        }
      });
      $('.notification_background').css('display', 'block');
    }else {
      $.ajax({
        type: "POST",
        url: "db/notifications.php",
        data: {id: "bellreload"},
        success: function(response) {
          $('.notification_bell').html(response);
          menu.classList.remove('show');
          menu.classList.add('hide');
          $('.notification_background').css('display', 'none');
        },
        error: function(xhr, status, error) {
            add_log("index: reload bell", "AJAX: "+error, "script.js", "./logs/", xhr.status);
            notifyshow(status+" ("+xhr.status+"): "+error, '');
        }
      });
    }
  };
})

Element.prototype.hasClass = function(class_name) {
    return this.class_name && new RegExp("(^|\\s)" + class_name + "(\\s|$)").test(this.class_name);
};
//select subject form
  const wrapper = document.querySelector(".select_subject_wrapper"),
  select_Btn = wrapper.querySelector(".select_subject_select_btn"),
  search_Inp = wrapper.querySelector(".select_subject_search input"),
  options = wrapper.querySelector(".select_subject_options");
  var subjects = [];
  var subjects_questions = [];
  var term_subject = [];
  $.ajax({
    url: 'db/subject_list.php',
    type: 'POST',
    dataType: 'json',
    success: function(response) {
      subjects = response.subjects;
      subjects_questions = response.subject_questions;
      if(subjects.length != 0){
        addSubject();
        subjects.forEach(subject => {
          term_subject.push(subject[0]);
        });
      }else{
        notifyshow(subjects_questions, '');
      }
    },
    error: function(xhr, status, error) {
        add_log("index: load subjects", "AJAX: "+error, "script.js", "./logs/", xhr.status);
        notifyshow(status+" ("+xhr.status+"): "+error, '');
    }
  });
  function addSubject(selected_subject) {
    options.innerHTML = "";
    let term = 0;
    subjects.forEach(subject => {
      var name_subject = subject[0];
      let li;
      let is_selected = name_subject == selected_subject ? "selected subject_li" : "subject_li";
      if(term != subject[1]){
        term=subject[1];
        li = `<li class="subject_li_term">${lang_text['quiz']['select_quiz']['term']} ${term}</li>
        <li onclick="updateName(this)" class="${is_selected}">${name_subject}</li>`;
      }else{
        li = `<li onclick="updateName(this)" class="${is_selected}">${name_subject}</li>`;
      }
      options.insertAdjacentHTML("beforeend", li);
    });
    document.querySelector(".select_subject_submit").style.display = "none";
    document.querySelector(".select_subject_numberOfQuestions_free").style.display = "none";
    document.querySelectorAll(".radio_buttons input").forEach(radio => {
      radio.checked = false;
    });
    document.querySelector("#custom_numberOfQuestions").value = "";
    document.querySelector(".select_subject_numberOfQuestions_max").innerHTML = parseInt($('.select_subject_select_btn input').attr("max"));
    document.querySelector("#custom_numberOfQuestions").max = parseInt($('.select_subject_select_btn input').attr("max"));
    document.querySelector(".select_subject_numberOfQuestions_max").addEventListener("click", () => {
      document.querySelector("#custom_numberOfQuestions").value = parseInt($('.select_subject_select_btn input').attr("max"));
      document.querySelector("#custom_numberOfQuestions").dispatchEvent(new Event('change'));
    });
  }
  document.querySelector("#custom_numberOfQuestions").addEventListener("change", () => {
    let min_question_value = parseInt($('#custom_numberOfQuestions').attr("min"));
    let max_question_value = parseInt($('#custom_numberOfQuestions').attr("max"));
    let value_question = Math.round(parseInt(document.querySelector("#custom_numberOfQuestions").value));
    if(value_question >= min_question_value && value_question <= max_question_value) {
      document.querySelector(".select_subject_submit").style.display = "block";
      document.querySelector("#custom_numberOfQuestions").value = value_question;
    }else{
      if(document.querySelector(".select_subject_submit").style.display != "block"){
        document.querySelector(".select_subject_submit").style.display = "block";
      }
      if(value_question < min_question_value) {
        document.querySelector("#custom_numberOfQuestions").value = min_question_value;
      }else{
        document.querySelector("#custom_numberOfQuestions").value = max_question_value;
      }
    }
    if(isNaN(value_question)){
      document.querySelector("#custom_numberOfQuestions").value = "";
    }
  });
  search_Inp.addEventListener("keyup", () => {
    var arr = [];
    let search_word = search_Inp.value.toLowerCase().trim();
    arr = term_subject.filter(data => {
      return data.toLowerCase().replace(/\s/g, '').includes(search_word);
    }).map(data => {
      let is_selected = data == select_Btn.firstElementChild.innerText ? "selected subject_li" : "subject_li";
      return `<li onclick="updateName(this)" class="${is_selected}">${data}</li>`;
    }).join("");
    options.innerHTML = arr ? arr : `<p style="margin-top: 10px;">Brak quizu</p>`;
    if(search_Inp.value == ""){
      let selected_subject = arr.match(/(?<=selected subject_li">)(.*?)(?=<\/li>)/g);
      if(selected_subject == null){
        addSubject();
      }else{
        addSubject(selected_subject[0]);
      }
    }
  });
  select_Btn.addEventListener("click", () => {
    wrapper.classList.toggle("active");
    document.querySelector('.quiz_score').style.display = "none";
    $('#close_save').removeAttr('onclick');
  });
  document.addEventListener("click", (e) => {
    if(e.target !== select_Btn && e.target !== search_Inp && e.target !== wrapper.querySelector(".select_subject_select_btn span") && e.target !== wrapper.querySelector(".select_subject_select_btn i") && e.target !== wrapper.querySelector(".select_subject_content") && e.target !== wrapper.querySelector(".subject_li_term") && e.target !== wrapper.querySelector(".select_subject_options")){
      wrapper.classList.remove("active");
    }
  });
function updateName(selected_li) {
  document.querySelector(".select_subject_numberOfQuestions").style.display = "block";
  document.querySelector('.select_subject_select_btn input').max = subjects_questions.find(sq => sq[0] === selected_li.innerText)[1];
  search_Inp.value = "";
  addSubject(selected_li.innerText);
  wrapper.classList.remove("active");
  select_Btn.firstElementChild.innerText = selected_li.innerText;
}
document.querySelectorAll('input[type="radio"]').forEach((item) => {
  item.addEventListener('click', () => {
    if(item == document.getElementById('numberOfQuestions_free')) {
      document.querySelector('.select_subject_numberOfQuestions_free').style.display = 'block';
      document.querySelector("#custom_numberOfQuestions").value = '';
      document.querySelector(".select_subject_submit").style.display = "none";
    }else{
      document.querySelector('.select_subject_numberOfQuestions_free').style.display = 'none';
      document.querySelector(".select_subject_submit").style.display = "block";
      let val = item.value;
      document.querySelector("#custom_numberOfQuestions").value = val;
    }
  });
});
