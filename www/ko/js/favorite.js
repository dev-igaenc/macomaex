(function () {
  var toastEl = null;
  var toastTimer = null;
  var anchorBtn = null;
  var REQUEST_URLS = (function () {
    var candidates = [];
    var resolved = "";

    try {
      resolved = new URL("../get_set.php", window.location.href).pathname;
    } catch (e) {
      resolved = "../get_set.php";
    }

    [resolved, "/get_set.php", "../get_set.php"].forEach(function (url) {
      if (candidates.indexOf(url) === -1) {
        candidates.push(url);
      }
    });

    return candidates;
  })();

  function showToast(icon, message, type) {
    if (toastEl) {
      toastEl.remove();
      clearTimeout(toastTimer);
    }

    toastEl = document.createElement("div");
    toastEl.className = "toast-notify toast-" + (type || "info");
    toastEl.innerHTML = '<span class="toast-icon">' + icon + '</span><span class="toast-msg">' + message + "</span>";

    if (anchorBtn) {
      toastEl.classList.add("toast-anchored");
      anchorBtn.style.position = anchorBtn.style.position || "relative";
      var parent = anchorBtn.parentElement;
      if (parent) {
        parent.style.position = parent.style.position || "relative";
        parent.appendChild(toastEl);
      } else {
        document.body.appendChild(toastEl);
      }
    } else {
      document.body.appendChild(toastEl);
    }

    requestAnimationFrame(function () {
      toastEl.classList.add("is-show");
    });

    toastTimer = setTimeout(function () {
      if (!toastEl) return;
      toastEl.classList.remove("is-show");
      setTimeout(function () {
        if (toastEl) toastEl.remove();
        toastEl = null;
      }, 300);
    }, 2200);
  }

  function getLoginUrl() {
    return "/bbs/login.php?url=" + encodeURIComponent(window.location.pathname + window.location.search + window.location.hash);
  }

  function splitRes(text) {
    return String(text || "").split("|");
  }

  function requestFavorite(url, kedcd, active) {
    if (window.jQuery && typeof window.jQuery.post === "function") {
      return new Promise(function (resolve, reject) {
        window.jQuery
          .post(url, {
            type: "favorite",
            kedcd: kedcd,
            chk: active
          })
          .done(function (res) {
            resolve(String(res || ""));
          })
          .fail(function (_jqXHR, _textStatus, errorThrown) {
            reject(errorThrown || new Error("favorite request failed"));
          });
      });
    }

    var body = new URLSearchParams();
    body.set("type", "favorite");
    body.set("kedcd", kedcd);
    body.set("chk", active ? "true" : "false");

    return fetch(url, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
      },
      body: body.toString()
    }).then(function (res) {
      return res.text();
    });
  }

  function postFavorite(kedcd, active) {
    var index = 0;

    function tryNext() {
      if (index >= REQUEST_URLS.length) {
        return Promise.resolve("500");
      }

      var url = REQUEST_URLS[index++];
      return requestFavorite(url, kedcd, active).then(function (res) {
        var code = splitRes(res)[0];
        if (code === "1000" || code === "501") {
          return res;
        }
        return tryNext();
      }).catch(function () {
        return tryNext();
      });
    }

    return tryNext();
  }

  var starOn = '<svg viewBox="0 0 24 24" fill="#fbbf24" stroke="#fbbf24" stroke-width="1.5" width="20" height="20"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
  var starOff = '<svg viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.5" width="20" height="20"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

  function setFavoriteState(kedcd, active) {
    if (!kedcd) return;
    document.querySelectorAll('[data-favorite][data-kedcd="' + kedcd + '"]').forEach(function (el) {
      el.classList.toggle("is-active", active);
    });
  }

  function notifyParentFavoriteChanged(kedcd, active) {
    if (!kedcd) return;
    if (window.parent && window.parent !== window) {
      window.parent.postMessage({
        type: "favorite-changed",
        kedcd: kedcd,
        active: !!active
      }, window.location.origin);
    }
  }

  function notifyLocalFavoriteChanged(kedcd, active) {
    if (!kedcd) return;

    window.dispatchEvent(new CustomEvent("favorite:changed", {
      detail: {
        kedcd: kedcd,
        active: !!active
      }
    }));
  }

  function bindFavorites() {
    document.querySelectorAll("[data-favorite]").forEach(function (btn) {
      if (btn._favBound) return;
      btn._favBound = true;

      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var kedcd = btn.getAttribute("data-kedcd");
        if (!kedcd) return;

        var nextActive = !btn.classList.contains("is-active");
        anchorBtn = btn;

        postFavorite(kedcd, nextActive)
          .then(function (res) {
            var code = splitRes(res)[0];

            if (code === "1000") {
              setFavoriteState(kedcd, nextActive);
              notifyLocalFavoriteChanged(kedcd, nextActive);
              notifyParentFavoriteChanged(kedcd, nextActive);
              if (nextActive) {
                showToast(starOn, "관심기업으로 등록되었습니다", "success");
              } else {
                showToast(starOff, "관심기업에서 해제되었습니다", "default");
              }
              return;
            }

            if (code === "501") {
              window.location.href = getLoginUrl();
              return;
            }

            showToast(starOff, "관심기업 처리 중 오류가 발생했습니다", "default");
          })
          .catch(function () {
            showToast(starOff, "관심기업 처리 중 오류가 발생했습니다", "default");
          });
      });
    });
  }

  window._showToast = showToast;
  window._bindFavorites = bindFavorites;

  bindFavorites();
})();
