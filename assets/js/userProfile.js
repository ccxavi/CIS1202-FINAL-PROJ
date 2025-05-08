let userProfile = document.getElementById("auth");
let userProfileBar = document.getElementById("userProfileBar")


document.addEventListener("click", function(event){
    if (userProfile.contains(event.target)){
        // if (userProfileBar.contains(event.target)){
        //     userProfileBar.classList.remove('show');
        //     return;
        // }
        console.log("Yes");
        userProfileBar.classList.add('show');
        return;

    } else if (userProfileBar.classList.contains('show')){
        if (!userProfileBar.contains(event.target)){
            userProfileBar.classList.remove('show');
        }
        return;
    }
})

