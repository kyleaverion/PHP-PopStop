' MainForm.vb
Imports System.Drawing
Imports System.Windows.Forms

Public Class MainForm
    Inherits Form

    Private logo As PictureBox
    Private lblTitle As Label
    Private btnHome As Button
    Private btnPackages As Button
    Private btnAboutUs As Button
    Private btnMenu As Button
    Private btnAdventurerHome As Button ' Added this button

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

    Public Sub New()
        InitializeComponent()
        SetupForm()
        CreateControls()
        SetupEventHandlers()
    End Sub

    Private Sub InitializeComponent()
        Me.SuspendLayout()
        Me.AutoScaleDimensions = New SizeF(8.0F, 16.0F)
        Me.AutoScaleMode = AutoScaleMode.Font
        Me.ClientSize = New Size(1200, 800)
        Me.Text = "LakbayPH Travel & Tours"
        Me.StartPosition = FormStartPosition.CenterScreen
        Me.WindowState = FormWindowState.Maximized
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
        btnHome = New Button()
        btnHome.Text = "Home"
        btnHome.Font = New Font("Arial", 10, FontStyle.Regular)
        btnHome.ForeColor = Color.White
        btnHome.BackColor = Color.Transparent
        btnHome.FlatStyle = FlatStyle.Flat
        btnHome.FlatAppearance.BorderSize = 0
        btnHome.FlatAppearance.MouseOverBackColor = Color.FromArgb(60, 100, 110)
        btnHome.Location = New Point(400, 25)
        btnHome.Size = New Size(80, 30)
        Me.Controls.Add(btnHome)

        btnPackages = New Button()
        btnPackages.Text = "Packages"
        btnPackages.Font = New Font("Arial", 10, FontStyle.Regular)
        btnPackages.ForeColor = Color.White
        btnPackages.BackColor = Color.Transparent
        btnPackages.FlatStyle = FlatStyle.Flat
        btnPackages.FlatAppearance.BorderSize = 0
        btnPackages.FlatAppearance.MouseOverBackColor = Color.FromArgb(60, 100, 110)
        btnPackages.Location = New Point(500, 25)
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
        btnAboutUs.Location = New Point(600, 25)
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
        btnAdventurerHome.Location = New Point(700, 25)
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

        ' Username textbox
        txtUsername = New TextBox()
        txtUsername.Font = New Font("Arial", 12)
        txtUsername.Location = New Point(20, 90)
        txtUsername.Size = New Size(260, 30)
        txtUsername.BackColor = Color.FromArgb(60, 100, 110)
        txtUsername.ForeColor = Color.White
        txtUsername.BorderStyle = BorderStyle.FixedSingle
        pnlLogin.Controls.Add(txtUsername)

        ' Password textbox
        txtPassword = New TextBox()
        txtPassword.Font = New Font("Arial", 12)
        txtPassword.Location = New Point(20, 140)
        txtPassword.Size = New Size(260, 30)
        txtPassword.BackColor = Color.FromArgb(60, 100, 110)
        txtPassword.ForeColor = Color.White
        txtPassword.BorderStyle = BorderStyle.FixedSingle
        txtPassword.UseSystemPasswordChar = True
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
        AddHandler btnHome.Click, AddressOf BtnHome_Click
        AddHandler btnPackages.Click, AddressOf BtnPackages_Click
        AddHandler btnAboutUs.Click, AddressOf BtnAboutUs_Click
        AddHandler btnSignUpLogin.Click, AddressOf BtnSignUp_Click
        AddHandler btnLogIn.Click, AddressOf BtnLogIn_Click
        AddHandler lblForgotPassword.Click, AddressOf LblForgotPassword_Click
        AddHandler btnMenu.Click, AddressOf BtnMenu_Click
        AddHandler btnAdventurerHome.Click, AddressOf BtnAdventurerHome_Click
    End Sub

    Private Sub BtnHome_Click(sender As Object, e As EventArgs)
        Dim homeForm As New HomeForm()
        homeForm.Show()
    End Sub

    Private Sub BtnPackages_Click(sender As Object, e As EventArgs)
        Dim packagesForm As New LakbayPHPackagesForm()
        packagesForm.Show()
        Me.Hide() ' Optional: Hide current form
    End Sub

    ' Fixed the adventurer button click function
    Private Sub BtnAdventurerHome_Click(sender As Object, e As EventArgs)
        Try
            Dim adventurerForm As New AdventurerHomeForm()
            adventurerForm.ShowDialog()
        Catch ex As Exception
            MessageBox.Show("Error opening Adventurer Home: " & ex.Message, "Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
        End Try
    End Sub

    Private Sub BtnAboutUs_Click(sender As Object, e As EventArgs)
        Dim aboutUsForm As New AboutUsForm()
        aboutUsForm.Show()
    End Sub

    Private Sub BtnSignUp_Click(sender As Object, e As EventArgs)
        Dim signUpForm As New SignUpForm()
        signUpForm.Show()
    End Sub

    Private Sub BtnLogIn_Click(sender As Object, e As EventArgs)
        ' Handle login logic here
        MessageBox.Show("Login functionality to be implemented", "Login", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub LblForgotPassword_Click(sender As Object, e As EventArgs)
        ' Handle forgot password logic here
        MessageBox.Show("Forgot password functionality to be implemented", "Forgot Password", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub

    Private Sub BtnMenu_Click(sender As Object, e As EventArgs)
        ' Handle menu toggle logic here
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
End Class