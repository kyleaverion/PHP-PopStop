Imports System.Drawing
Imports System.Windows.Forms
Imports MySqlConnector
Imports System.Security.Cryptography
Imports System.Text

Public Class MainForm
    Inherits Form

    ' Database connection
    Dim conn As MySqlConnection = New MySqlConnection("Server=localhost;Database=lakbayph_web;Uid=root;Pwd=;")
    Public sql As String
    Public dbcomm As MySqlCommand

    Private logo As PictureBox
    Private lblTitle As Label
    Private btnPackages As Button
    Private btnAboutUs As Button
    Private btnMenu As Button
    Private btnAdventurerHome As Button

    ' Login Panel Controls
    Private pnlLogin As Panel
    Private lblLogin As Label
    Private txtUsername As TextBox
    Private txtPassword As TextBox
    Private chkRememberMe As CheckBox
    Private lblForgotPassword As Label
    Private btnLogIn As Button
    Private lblOr As Label
    Private btnSignUpLogin As Button

    ' Main Content Panel
    Private pnlMainContent As Panel
    Private lblMainTitle As Label
    Private lblSubtitle As Label
    Private logoMain As PictureBox

    ' Login status tracking
    Private loggedInUser As UserInfo = Nothing

    ' User Info class to store logged-in user data
    Public Class UserInfo
        Public Property UserID As Integer
        Public Property Username As String
        Public Property Email As String
        Public Property FirstName As String
        Public Property LastName As String
        Public Property IsActive As Boolean
        Public Property CreatedAt As DateTime
    End Class

    Public Sub New()
        InitializeComponent()
        SetupForm()
        CreateControls()
        SetupEventHandlers()
    End Sub

    Private Sub InitializeComponent()
        Me.SuspendLayout()
        '
        'MainForm
        '
        Me.AutoScaleDimensions = New System.Drawing.SizeF(8.0!, 16.0!)
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(1200, 800)
        Me.Name = "MainForm"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen
        Me.Text = "LakbayPH Travel & Tours"
        Me.WindowState = System.Windows.Forms.FormWindowState.Maximized
        Me.ResumeLayout(False)
    End Sub

    Private Sub SetupForm()
        ' Set form background color (teal theme)
        Me.BackColor = Color.FromArgb(45, 85, 95)
        Me.FormBorderStyle = FormBorderStyle.Sizable
        Me.MinimumSize = New Size(1000, 600)
    End Sub

    Private Sub CreateControls()
        ' Create top navigation bar
        CreateTopNavigation()

        ' Create main content area
        CreateMainContent()

        ' Create login panel
        CreateLoginPanel()
    End Sub

    Private Sub CreateTopNavigation()
        ' Logo
        logo = New PictureBox()
        logo.Size = New Size(50, 50)
        logo.Location = New Point(20, 10)
        logo.BackColor = Color.White
        logo.BorderStyle = BorderStyle.FixedSingle
        Me.Controls.Add(logo)

        ' Title
        lblTitle = New Label()
        lblTitle.Text = "LakbayPH" & vbCrLf & "Travel & Tours"
        lblTitle.Font = New Font("Arial", 12, FontStyle.Bold)
        lblTitle.ForeColor = Color.White
        lblTitle.Location = New Point(80, 15)
        lblTitle.Size = New Size(150, 40)
        Me.Controls.Add(lblTitle)

        ' Navigation buttons
        btnPackages = New Button()
        btnPackages.Text = "Packages"
        btnPackages.Font = New Font("Arial", 10, FontStyle.Regular)
        btnPackages.ForeColor = Color.White
        btnPackages.BackColor = Color.Transparent
        btnPackages.FlatStyle = FlatStyle.Flat
        btnPackages.FlatAppearance.BorderSize = 0
        btnPackages.FlatAppearance.MouseOverBackColor = Color.FromArgb(60, 100, 110)
        btnPackages.Location = New Point(400, 25)
        btnPackages.Size = New Size(80, 30)
        Me.Controls.Add(btnPackages)

        btnAboutUs = New Button()
        btnAboutUs.Text = "About Us"
        btnAboutUs.Font = New Font("Arial", 10, FontStyle.Regular)
        btnAboutUs.ForeColor = Color.White
        btnAboutUs.BackColor = Color.Transparent
        btnAboutUs.FlatStyle = FlatStyle.Flat
        btnAboutUs.FlatAppearance.BorderSize = 0
        btnAboutUs.FlatAppearance.MouseOverBackColor = Color.FromArgb(60, 100, 110)
        btnAboutUs.Location = New Point(500, 25)
        btnAboutUs.Size = New Size(80, 30)
        Me.Controls.Add(btnAboutUs)

        ' Adventurer Home button
        btnAdventurerHome = New Button()
        btnAdventurerHome.Text = "Reviews"
        btnAdventurerHome.Font = New Font("Arial", 10, FontStyle.Regular)
        btnAdventurerHome.ForeColor = Color.White
        btnAdventurerHome.BackColor = Color.Transparent
        btnAdventurerHome.FlatStyle = FlatStyle.Flat
        btnAdventurerHome.FlatAppearance.BorderSize = 0
        btnAdventurerHome.FlatAppearance.MouseOverBackColor = Color.FromArgb(60, 100, 110)
        btnAdventurerHome.Location = New Point(600, 25)
        btnAdventurerHome.Size = New Size(90, 30)
        Me.Controls.Add(btnAdventurerHome)

        ' Menu button (hamburger menu)
        btnMenu = New Button()
        btnMenu.Text = "☰"
        btnMenu.Font = New Font("Arial", 16, FontStyle.Bold)
        btnMenu.ForeColor = Color.White
        btnMenu.BackColor = Color.Transparent
        btnMenu.FlatStyle = FlatStyle.Flat
        btnMenu.FlatAppearance.BorderSize = 0
        btnMenu.Location = New Point(Me.Width - 60, 20)
        btnMenu.Size = New Size(40, 35)
        btnMenu.Anchor = AnchorStyles.Top Or AnchorStyles.Right
        Me.Controls.Add(btnMenu)
    End Sub

    Private Sub CreateMainContent()
        ' Main content panel
        pnlMainContent = New Panel()
        pnlMainContent.BackColor = Color.FromArgb(100, 255, 255, 255) ' Semi-transparent white
        pnlMainContent.Location = New Point(50, 120)
        pnlMainContent.Size = New Size(500, 400)
        Me.Controls.Add(pnlMainContent)

        ' Main title
        lblMainTitle = New Label()
        lblMainTitle.Text = "EXPLORE YOUR" & vbCrLf & "DREAM PLACE" & vbCrLf & "WITH US"
        lblMainTitle.Font = New Font("Arial", 28, FontStyle.Bold)
        lblMainTitle.ForeColor = Color.White
        lblMainTitle.Location = New Point(20, 30)
        lblMainTitle.Size = New Size(460, 150)
        pnlMainContent.Controls.Add(lblMainTitle)

        ' Subtitle
        lblSubtitle = New Label()
        lblSubtitle.Text = "Make the experience of traveling to your dream" & vbCrLf &
                          "tourist destination come true with us. We will provide" & vbCrLf &
                          "the best experience of your life."
        lblSubtitle.Font = New Font("Arial", 12, FontStyle.Regular)
        lblSubtitle.ForeColor = Color.White
        lblSubtitle.Location = New Point(20, 200)
        lblSubtitle.Size = New Size(460, 80)
        pnlMainContent.Controls.Add(lblSubtitle)

        ' Logo in main content
        logoMain = New PictureBox()
        logoMain.Size = New Size(80, 80)
        logoMain.Location = New Point(20, 300)
        logoMain.BackColor = Color.White
        logoMain.BorderStyle = BorderStyle.FixedSingle
        pnlMainContent.Controls.Add(logoMain)
    End Sub

    Private Sub CreateLoginPanel()
        ' Login panel
        pnlLogin = New Panel()
        pnlLogin.BackColor = Color.FromArgb(150, 30, 70, 80) ' Semi-transparent dark teal
        pnlLogin.Location = New Point(Me.Width - 350, 120)
        pnlLogin.Size = New Size(300, 400)
        pnlLogin.Anchor = AnchorStyles.Top Or AnchorStyles.Right
        Me.Controls.Add(pnlLogin)

        ' Login title with airplane icon
        lblLogin = New Label()
        lblLogin.Text = "Log in ✈"
        lblLogin.Font = New Font("Arial", 18, FontStyle.Bold)
        lblLogin.ForeColor = Color.White
        lblLogin.Location = New Point(20, 30)
        lblLogin.Size = New Size(260, 40)
        pnlLogin.Controls.Add(lblLogin)

        ' Username textbox with placeholder
        txtUsername = New TextBox()
        txtUsername.Font = New Font("Arial", 12)
        txtUsername.Location = New Point(20, 90)
        txtUsername.Size = New Size(260, 30)
        txtUsername.BackColor = Color.FromArgb(60, 100, 110)
        txtUsername.ForeColor = Color.White
        txtUsername.BorderStyle = BorderStyle.FixedSingle
        txtUsername.Text = "Username or Email"
        txtUsername.ForeColor = Color.LightGray
        pnlLogin.Controls.Add(txtUsername)

        ' Password textbox with placeholder
        txtPassword = New TextBox()
        txtPassword.Font = New Font("Arial", 12)
        txtPassword.Location = New Point(20, 140)
        txtPassword.Size = New Size(260, 30)
        txtPassword.BackColor = Color.FromArgb(60, 100, 110)
        txtPassword.ForeColor = Color.White
        txtPassword.BorderStyle = BorderStyle.FixedSingle
        txtPassword.Text = "Password"
        txtPassword.ForeColor = Color.LightGray
        pnlLogin.Controls.Add(txtPassword)

        ' Remember me checkbox
        chkRememberMe = New CheckBox()
        chkRememberMe.Text = "REMEMBER ME"
        chkRememberMe.Font = New Font("Arial", 9)
        chkRememberMe.ForeColor = Color.White
        chkRememberMe.Location = New Point(20, 180)
        chkRememberMe.Size = New Size(130, 20)
        pnlLogin.Controls.Add(chkRememberMe)

        ' Forgot password label
        lblForgotPassword = New Label()
        lblForgotPassword.Text = "FORGOT PASSWORD?"
        lblForgotPassword.Font = New Font("Arial", 9)
        lblForgotPassword.ForeColor = Color.White
        lblForgotPassword.Location = New Point(160, 180)
        lblForgotPassword.Size = New Size(120, 20)
        lblForgotPassword.Cursor = Cursors.Hand
        pnlLogin.Controls.Add(lblForgotPassword)

        ' Log in button
        btnLogIn = New Button()
        btnLogIn.Text = "LOG IN"
        btnLogIn.Font = New Font("Arial", 12, FontStyle.Bold)
        btnLogIn.ForeColor = Color.FromArgb(30, 70, 80)
        btnLogIn.BackColor = Color.White
        btnLogIn.FlatStyle = FlatStyle.Flat
        btnLogIn.FlatAppearance.BorderColor = Color.White
        btnLogIn.Location = New Point(20, 220)
        btnLogIn.Size = New Size(260, 40)
        pnlLogin.Controls.Add(btnLogIn)

        ' OR label
        lblOr = New Label()
        lblOr.Text = "OR"
        lblOr.Font = New Font("Arial", 12, FontStyle.Bold)
        lblOr.ForeColor = Color.White
        lblOr.Location = New Point(140, 280)
        lblOr.Size = New Size(30, 20)
        lblOr.TextAlign = ContentAlignment.MiddleCenter
        pnlLogin.Controls.Add(lblOr)

        ' Sign up button in login panel
        btnSignUpLogin = New Button()
        btnSignUpLogin.Text = "SIGN UP"
        btnSignUpLogin.Font = New Font("Arial", 12, FontStyle.Bold)
        btnSignUpLogin.ForeColor = Color.White
        btnSignUpLogin.BackColor = Color.Transparent
        btnSignUpLogin.FlatStyle = FlatStyle.Flat
        btnSignUpLogin.FlatAppearance.BorderColor = Color.White
        btnSignUpLogin.Location = New Point(20, 320)
        btnSignUpLogin.Size = New Size(260, 40)
        pnlLogin.Controls.Add(btnSignUpLogin)
    End Sub

    Private Sub SetupEventHandlers()
        AddHandler btnPackages.Click, AddressOf BtnPackages_Click
        AddHandler btnAboutUs.Click, AddressOf BtnAboutUs_Click
        AddHandler btnSignUpLogin.Click, AddressOf BtnSignUp_Click
        AddHandler btnLogIn.Click, AddressOf BtnLogIn_Click
        AddHandler lblForgotPassword.Click, AddressOf LblForgotPassword_Click
        AddHandler btnMenu.Click, AddressOf BtnMenu_Click
        AddHandler btnAdventurerHome.Click, AddressOf BtnAdventurerHome_Click

        ' Add placeholder handlers for username and password textboxes
        AddHandler txtUsername.GotFocus, AddressOf TxtUsername_GotFocus
        AddHandler txtUsername.LostFocus, AddressOf TxtUsername_LostFocus
        AddHandler txtPassword.GotFocus, AddressOf TxtPassword_GotFocus
        AddHandler txtPassword.LostFocus, AddressOf TxtPassword_LostFocus

        ' Add Enter key handler for login
        AddHandler txtUsername.KeyPress, AddressOf TextBox_KeyPress
        AddHandler txtPassword.KeyPress, AddressOf TextBox_KeyPress
    End Sub

    ' Placeholder handlers for username textbox
    Private Sub TxtUsername_GotFocus(sender As Object, e As EventArgs)
        If txtUsername.Text = "Username or Email" Then
            txtUsername.Text = ""
            txtUsername.ForeColor = Color.White
        End If
    End Sub

    Private Sub TxtUsername_LostFocus(sender As Object, e As EventArgs)
        If String.IsNullOrWhiteSpace(txtUsername.Text) Then
            txtUsername.Text = "Username or Email"
            txtUsername.ForeColor = Color.LightGray
        End If
    End Sub

    ' Placeholder handlers for password textbox
    Private Sub TxtPassword_GotFocus(sender As Object, e As EventArgs)
        If txtPassword.Text = "Password" Then
            txtPassword.Text = ""
            txtPassword.ForeColor = Color.White
            txtPassword.UseSystemPasswordChar = True
        End If
    End Sub

    Private Sub TxtPassword_LostFocus(sender As Object, e As EventArgs)
        If String.IsNullOrWhiteSpace(txtPassword.Text) Then
            txtPassword.Text = "Password"
            txtPassword.ForeColor = Color.LightGray
            txtPassword.UseSystemPasswordChar = False
        End If
    End Sub

    ' Handle Enter key press in textboxes
    Private Sub TextBox_KeyPress(sender As Object, e As KeyPressEventArgs)
        If e.KeyChar = Chr(13) Then ' Enter key
            BtnLogIn_Click(sender, e)
        End If
    End Sub

    ' Updated Login Button Click Event with better error handling
    Private Sub BtnLogIn_Click(sender As Object, e As EventArgs)
        Try
            ' Validate input
            If Not ValidateLoginInput() Then
                Return
            End If

            ' Get username/email and password
            Dim usernameOrEmail As String = txtUsername.Text.Trim()
            Dim password As String = txtPassword.Text

            ' Authenticate user
            Dim user As UserInfo = AuthenticateUser(usernameOrEmail, password)

            If user IsNot Nothing Then
                ' Login successful
                loggedInUser = user

                ' Show success message
                MessageBox.Show($"Welcome back, {user.FirstName}! Redirecting to home page...", "Login Successful",
                              MessageBoxButtons.OK, MessageBoxIcon.Information)

                ' Create and show TravelHomepageForm
                Try
                    ' Check if TravelHomepageForm class exists and has the correct constructor
                    Dim homeForm As TravelHomepageForm = New TravelHomepageForm(user)

                    ' Hide current form before showing new one
                    Me.Hide()

                    ' Show the new form
                    homeForm.Show()

                    ' Optional: Close this form completely instead of just hiding
                    Me.Close()

                Catch ex As Exception
                    ' If there's an error creating TravelHomepageForm, show this form again
                    Me.Show()
                    MessageBox.Show($"Error opening Travel Homepage: {ex.Message}", "Navigation Error",
                                  MessageBoxButtons.OK, MessageBoxIcon.Error)
                End Try

            Else
                ' Login failed
                MessageBox.Show("Invalid username/email or password. Please try again.",
                              "Login Failed", MessageBoxButtons.OK, MessageBoxIcon.Warning)

                ' Clear password field
                txtPassword.Text = "Password"
                txtPassword.ForeColor = Color.LightGray
                txtPassword.UseSystemPasswordChar = False
                txtUsername.Focus()
            End If

        Catch ex As Exception
            MessageBox.Show($"Login error: {ex.Message}", "Error",
                          MessageBoxButtons.OK, MessageBoxIcon.Error)
        End Try
    End Sub

    ' Validate login input
    Private Function ValidateLoginInput() As Boolean
        ' Check if username/email is entered
        If txtUsername.Text = "Username or Email" Or String.IsNullOrWhiteSpace(txtUsername.Text) Then
            MessageBox.Show("Please enter your username or email address.",
                          "Login Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtUsername.Focus()
            Return False
        End If

        ' Check if password is entered
        If txtPassword.Text = "Password" Or String.IsNullOrWhiteSpace(txtPassword.Text) Then
            MessageBox.Show("Please enter your password.",
                          "Login Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtPassword.Focus()
            Return False
        End If

        Return True
    End Function

    ' Authenticate user against database
    Private Function AuthenticateUser(usernameOrEmail As String, password As String) As UserInfo
        Try
            ' Open connection
            If conn.State = ConnectionState.Closed Then
                conn.Open()
            End If

            ' Hash the provided password to compare with stored hash
            Dim hashedPassword As String = HashPassword(password)

            ' Query to find user by username or email
            Dim query As String = "SELECT UserID, Username, Email, Password_, FirstName, LastName, IsActive, CreatedAt " &
                                 "FROM userss WHERE (Username = @UsernameOrEmail OR Email = @UsernameOrEmail) AND IsActive = 1"

            Using dbcomm As New MySqlCommand(query, conn)
                dbcomm.Parameters.AddWithValue("@UsernameOrEmail", usernameOrEmail.ToLower())

                Using reader As MySqlDataReader = dbcomm.ExecuteReader()
                    If reader.Read() Then
                        ' Check if password matches
                        Dim storedPassword As String = reader("Password_").ToString()

                        If storedPassword = hashedPassword Then
                            ' Password matches, create user info object
                            Dim user As New UserInfo() With {
                                .UserID = Convert.ToInt32(reader("UserID")),
                                .Username = reader("Username").ToString(),
                                .Email = reader("Email").ToString(),
                                .FirstName = reader("FirstName").ToString(),
                                .LastName = reader("LastName").ToString(),
                                .IsActive = Convert.ToBoolean(reader("IsActive")),
                                .CreatedAt = Convert.ToDateTime(reader("CreatedAt"))
                            }

                            Return user
                        End If
                    End If
                End Using
            End Using

            ' User not found or password doesn't match
            Return Nothing

        Catch ex As Exception
            MessageBox.Show($"Authentication error: {ex.Message}", "Database Error",
                          MessageBoxButtons.OK, MessageBoxIcon.Error)
            Return Nothing
        Finally
            ' Close connection
            If conn.State = ConnectionState.Open Then
                conn.Close()
            End If
        End Try
    End Function

    ' Hash password using SHA256
    Private Function HashPassword(password As String) As String
        Try
            If String.IsNullOrEmpty(password) Then
                Throw New ArgumentException("Password cannot be null or empty")
            End If

            Using sha256Hash As SHA256 = SHA256.Create()
                Dim bytes As Byte() = sha256Hash.ComputeHash(Encoding.UTF8.GetBytes(password))
                Dim builder As New StringBuilder(bytes.Length * 2)
                For Each b As Byte In bytes
                    builder.Append(b.ToString("x2"))
                Next
                Return builder.ToString()
            End Using

        Catch ex As Exception
            Throw New InvalidOperationException("Password hashing failed", ex)
        End Try
    End Function

    Private Sub BtnPackages_Click(sender As Object, e As EventArgs)
        Try
            Dim packagesForm As New PackagesWithoutUserForm()
            Me.Hide()
            packagesForm.ShowDialog()
            Me.Show()
        Catch ex As Exception
            MessageBox.Show($"Error opening Packages: {ex.Message}", "Navigation Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
            Me.Show()
        End Try
    End Sub

    Private Sub BtnAdventurerHome_Click(sender As Object, e As EventArgs)
        Try
            Dim adventurerForm As New AdventurerHomeForm()
            Me.Hide()
            adventurerForm.ShowDialog()
            Me.Show()
        Catch ex As Exception
            MessageBox.Show("Error opening Adventurer Home: " & ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
            Me.Show()
        End Try
    End Sub

    Private Sub BtnAboutUs_Click(sender As Object, e As EventArgs)
        Try
            Dim aboutUsForm As New AboutUsForm()
            Me.Hide()
            aboutUsForm.ShowDialog()
            Me.Show()
        Catch ex As Exception
            MessageBox.Show($"Error opening About Us: {ex.Message}", "Navigation Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
            Me.Show()
        End Try
    End Sub

    Private Sub BtnSignUp_Click(sender As Object, e As EventArgs)
        Try
            Dim signUpForm As New SignUpForm()
            Me.Hide()
            signUpForm.ShowDialog()
            Me.Show()
        Catch ex As Exception
            MessageBox.Show($"Error opening Sign Up: {ex.Message}", "Navigation Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
            Me.Show()
        End Try
    End Sub

    Private Sub LblForgotPassword_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Forgot password functionality to be implemented", "Forgot Password", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub BtnMenu_Click(sender As Object, e As EventArgs)
        MessageBox.Show("Menu functionality to be implemented", "Menu", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Protected Overrides Sub OnResize(e As EventArgs)
        MyBase.OnResize(e)
        If pnlLogin IsNot Nothing Then
            pnlLogin.Location = New Point(Me.Width - 350, 120)
        End If
        If btnMenu IsNot Nothing Then
            btnMenu.Location = New Point(Me.Width - 60, 20)
        End If
    End Sub

    ' Property to get current logged-in user
    Public ReadOnly Property CurrentUser As UserInfo
        Get
            Return loggedInUser
        End Get
    End Property

    ' Method to check if user is logged in
    Public Function IsUserLoggedIn() As Boolean
        Return loggedInUser IsNot Nothing
    End Function

    Private Sub MainForm_Load(sender As Object, e As EventArgs) Handles MyBase.Load
    End Sub
End Class