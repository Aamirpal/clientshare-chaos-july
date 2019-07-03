
function searchShare() {
    var searchInput, filter, shareList, arrayShare, currentShare, index;
    searchInput = document.getElementById("search_share");
    filter = searchInput.value.toUpperCase();
    shareList = document.getElementById("search_share_ul");
    arrayShare = shareList.getElementsByTagName("li");
    for (index = 0; index < arrayShare.length; index++) {
        currentShare = arrayShare[index].getAttribute('value');
        if (currentShare.toUpperCase().indexOf(filter) > -1) {
            arrayShare[index].style.display = "";
        } else {
            arrayShare[index].style.display = "none";

        }
    }
}