const { createApp, ref, computed, onMounted } = Vue;

createApp({
  setup() {
    // Core View State
    // Controls which "page" or section is currently visible to the user.
    const currentView = ref("login");

    // FEATURE: AUTHENTICATION
    // Handles login, registration, and session persistence.

    const currentUser = ref(null); // The logged-in admin or user object
    const authError = ref("");
    const successMessage = ref("");

    // Form states for Auth
    const loginForm = ref({ email: "", password: "" });
    const registerForm = ref({ name: "", email: "", password: "", avatar: "" });

    // UX: Toggles for password visibility icons
    const showLoginPass = ref(false);
    const showRegPass = ref(false);

    // API Call: Login
    const handleLogin = async () => {
      authError.value = ""; // Reset error state
      try {
        const res = await axios.post("myapi/login.php", loginForm.value);

        // Check if the backend returned a valid user object
        if (res.data && res.data.user) {
          currentUser.value = res.data.user;

          // Persist session to LocalStorage so refresh doesn't log them out
          localStorage.setItem("user", JSON.stringify(res.data.user));

          // Navigate to dashboard and load data
          currentView.value = "dashboard";
          fetchUsers();
        } else {
          authError.value = "Login failed: No user data received.";
        }
      } catch (e) {
        authError.value = "Invalid credentials or server error.";
      }
    };

    // API Call: Register
    const handleRegister = async () => {
      try {
        const res = await axios.post("myapi/create.php", registerForm.value);

        // Handle both 200 (OK) and 201 (Created)
        if (res.status === 200 || res.status === 201) {
          successMessage.value = "Success! Redirecting...";

          // UX: Brief delay before switching views to let user read the success message
          setTimeout(() => {
            currentView.value = "login";
            // Clean up form
            registerForm.value = {
              name: "",
              email: "",
              password: "",
              avatar: "",
            };
            previewImage.value = null;
            successMessage.value = "";
          }, 2000);
        }
      } catch (e) {
        authError.value = "Registration failed. Email might be in use.";
      }
    };

    const logout = () => {
      currentUser.value = null;
      localStorage.removeItem("user");
      currentView.value = "login";
    };

    // FEATURE: USER MANAGEMENT
    // CRUD operations for the Admin dashboard.

    const users = ref([]);
    const selectedUser = ref(null); // For the "View Details" page
    const selectedIds = ref([]); // For batch actions (checkboxes)
    const searchQuery = ref("");

    // Modal & Form State
    const showUserModal = ref(false);
    const isUserEditing = ref(false); // Distinguishes between "Create" and "Update" modes
    const showModalPass = ref(false);
    const previewImage = ref(null); // For image upload preview
    const modalError = ref("");

    // Reusable form object for both Create and Edit
    const form = ref({
      id: null,
      name: "",
      email: "",
      password: "",
      avatar: "",
    });

    // Computed Property: Filter users client-side based on search input
    // This avoids making a new API call for every keystroke.
    const filteredUsers = computed(() => {
      if (!users.value) return [];
      if (!searchQuery.value) return users.value;

      const lower = searchQuery.value.toLowerCase();
      return users.value.filter(
        (u) =>
          (u.name && u.name.toLowerCase().includes(lower)) ||
          (u.email && u.email.toLowerCase().includes(lower)),
      );
    });

    // Computed Property: "Select All" Checkbox logic
    const isAllSelected = computed(() => {
      if (!filteredUsers.value) return false;
      return (
        filteredUsers.value.length > 0 &&
        selectedIds.value.length === filteredUsers.value.length
      );
    });

    // Opens the modal in "Edit Mode" pre-filled with user data
    const editUser = (user) => {
      isUserEditing.value = true;
      previewImage.value = user.avatar;

      // Map the user data to the form
      // Note: We leave password blank so we don't accidentally overwrite it with a hash or empty string
      form.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        password: "",
        avatar: user.avatar,
      };
      showUserModal.value = true;
    };

    // Opens the modal in "Create Mode" with a blank form
    const openUserModal = () => {
      isUserEditing.value = false;
      previewImage.value = null;
      form.value = { id: null, name: "", email: "", password: "", avatar: "" };
      showUserModal.value = true;
    };

    // Handles both Creating and Updating users to keep logic centralized
    const saveUser = async () => {
      // Dynamic endpoint selection
      const url = isUserEditing.value ? "myapi/update.php" : "myapi/create.php";

      // Security: We inject the admin_id so the backend can verify permissions
      const payload = {
        ...form.value,
        admin_id: currentUser.value.id,
      };

      try {
        const res = await axios.post(url, payload);

        if (
          res.status === 200 ||
          res.status === 201 ||
          res.data.status === "success"
        ) {
          showUserModal.value = false;
          // Reset form state
          form.value = {
            id: null,
            name: "",
            email: "",
            password: "",
            avatar: "",
          };
          previewImage.value = null;
          fetchUsers(); // Refresh the list
          alert(
            isUserEditing.value
              ? "User updated successfully!"
              : "User created successfully!",
          );
        } else {
          modalError.value = res.data.message || "Error saving user.";
          alert(modalError.value);
        }
      } catch (e) {
        // Specific handling for "Forbidden" (403) errors
        if (e.response && e.response.status === 403) {
          alert("SECURITY ALERT: You are not authorized to edit users.");
        } else {
          modalError.value = "Error saving user.";
          alert("Operation failed. Email might already exist.");
        }
      }
    };

    const deleteUser = async (id) => {
      if (!confirm("Delete user?")) return;

      // Security: Always pass admin_id for deletion too
      try {
        await axios.post("myapi/delete.php", {
          id: id,
          admin_id: currentUser.value.id,
        });
        fetchUsers();
      } catch (e) {
        alert("Delete failed or unauthorized.");
      }
    };

    // Toggle User Status (Ban/Unban)
    const toggleStatus = async (user) => {
      if (user.id === currentUser.value.id) {
        alert("You cannot ban yourself!");
        return;
      }
      try {
        const res = await axios.post("myapi/toggle_status.php", {
          id: user.id,
          status: user.status || "active",
        });
        if (res.data.status === "success") user.status = res.data.new_status;
      } catch (e) {
        alert("Status update failed.");
      }
    };

    // Batch Actions
    const toggleSelectAll = () => {
      selectedIds.value =
        selectedIds.value.length === filteredUsers.value.length
          ? []
          : filteredUsers.value.map((u) => u.id);
    };

    const batchDelete = async () => {
      if (!confirm(`Delete ${selectedIds.value.length} users?`)) return;
      try {
        await axios.post("myapi/batch_delete.php", { ids: selectedIds.value });
        selectedIds.value = [];
        fetchUsers();
      } catch (e) {
        alert("Batch delete failed.");
      }
    };

    // FEATURE: SHARED NOTES
    // Logic for reading, creating, and editing notes.

    const notes = ref([]);
    const selectedNote = ref(null); // For the "Read Full Note" modal
    const showNoteModal = ref(false);
    const showViewNoteModal = ref(false);
    const isEditing = ref(false); // Distinguishes between Note Create/Edit

    const noteForm = ref({ id: null, title: "", content: "" });

    const openNoteModal = () => {
      isEditing.value = false; // Reset to Create Mode
      noteForm.value = { id: null, title: "", content: "" };
      showNoteModal.value = true;
    };

    const editNote = (note) => {
      isEditing.value = true;
      // Pre-fill form
      noteForm.value = {
        id: note.id,
        title: note.title,
        content: note.content,
      };
      showNoteModal.value = true;
    };

    const saveNote = async () => {
      if (!currentUser.value) return;
      const url = isEditing.value
        ? "myapi/notes_update.php"
        : "myapi/notes_create.php";

      try {
        const res = await axios.post(url, {
          id: noteForm.value.id,
          user_id: currentUser.value.id,
          title: noteForm.value.title,
          content: noteForm.value.content,
        });

        if (res.data.status === "success") {
          showNoteModal.value = false;
          noteForm.value = { id: null, title: "", content: "" };
          fetchNotes();
          alert(isEditing.value ? "Note updated!" : "Note published!");
        }
      } catch (e) {
        alert(e.response?.data?.message || "Failed to save note.");
      }
    };

    const deleteNote = async (note) => {
      if (!confirm("Are you sure you want to delete this note?")) return;
      try {
        const res = await axios.post("myapi/notes_delete.php", {
          note_id: note.id,
          user_id: currentUser.value.id,
        });
        if (res.data.status === "success") fetchNotes();
        else alert(res.data.message);
      } catch (e) {
        alert("Delete failed.");
      }
    };

    const viewNote = (note) => {
      selectedNote.value = note;
      showViewNoteModal.value = true;
    };

    // UTILITIES & DATA FETCHING

    const handleFileUpload = async (event, type) => {
      const file = event.target.files[0];
      if (!file) return;

      // Create a local preview immediately for better UX
      previewImage.value = URL.createObjectURL(file);

      const formData = new FormData();
      formData.append("avatar", file);

      try {
        const res = await axios.post("myapi/upload.php", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        if (res.data.status === "success") {
          if (type === "register")
            registerForm.value.avatar = res.data.filepath;
          else form.value.avatar = res.data.filepath;
        }
      } catch (e) {
        alert("Upload error.");
      }
    };

    const viewUserDetails = (user) => {
      selectedUser.value = user;
      currentView.value = "details";
      fetchNotes();
    };
    const backToDashboard = () => {
      selectedUser.value = null;
      currentView.value = "dashboard";
    };
    const showNotesView = () => {
      currentView.value = "notes";
      fetchNotes();
    };

    const fetchUsers = async () => {
      try {
        const res = await axios.get("myapi/read.php");
        users.value = Array.isArray(res.data) ? res.data : [];
      } catch (e) {
        users.value = [];
      }
    };

    const fetchNotes = async () => {
      try {
        const res = await axios.get("myapi/notes_read.php");
        notes.value = Array.isArray(res.data) ? res.data : [];
      } catch (e) {
        notes.value = [];
      }
    };

    const formatDate = (dateString) => {
      if (!dateString) return "N/A";
      return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    };

    // Initialize App
    onMounted(() => {
      const saved = localStorage.getItem("user");
      if (saved && saved !== "undefined") {
        try {
          currentUser.value = JSON.parse(saved);
          currentView.value = "dashboard";
          fetchUsers();
        } catch (e) {
          localStorage.removeItem("user");
        }
      }
    });

    // EXPORTS
    // Exposing state and functions to the HTML template

    return {
      // App State
      currentView,
      currentUser,
      selectedUser,
      selectedNote,

      // Data
      users,
      notes,
      searchQuery,
      filteredUsers,

      // Selection Logic
      selectedIds,
      isAllSelected,
      toggleSelectAll,

      // Modals & UI Toggles
      showUserModal,
      showNoteModal,
      showViewNoteModal,
      showLoginPass,
      showRegPass,
      showModalPass,
      isEditing,
      isUserEditing,
      previewImage,

      // Forms & Errors
      loginForm,
      registerForm,
      form,
      noteForm,
      authError,
      successMessage,
      modalError,

      // Actions
      handleLogin,
      handleRegister,
      logout,
      saveUser,
      editUser,
      deleteUser,
      batchDelete,
      saveNote,
      editNote,
      deleteNote,
      viewNote,

      // Helpers
      openUserModal,
      openNoteModal,
      viewUserDetails,
      backToDashboard,
      handleFileUpload,
      formatDate,
      toggleStatus,
      showNotesView,
    };
  },
}).mount("#app");
