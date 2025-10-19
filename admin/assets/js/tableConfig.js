const { deletePost } = require("../../../posterzadmin/backend/controllers/Apis/PostsApis");

const tableConfig = {
    users: {
        deleteFunction: deleteUser,
        deleteTitle: "Deleting User",
        deleteDescription: `
            <p><b>Do you really want to delete this <span class="text-danger">user</span>?</b></p>
            <small>Once you click delete, the action can’t be undone.</small>`
    },
    companies: {
        deleteFunction: deleteUser,
        deleteTitle: "Deleting Company",
        deleteDescription: `
            <p><b>Do you really want to delete this <span class="text-danger">company</span>?</b></p>
            <small>Once you click delete, the action can’t be undone.</small>`
    },
    
};