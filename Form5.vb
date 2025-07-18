Imports System.Drawing
Imports System.Windows.Forms
Imports MySqlConnector
Imports System.Security.Cryptography
Imports System.Text

Public Class SignUpForm
    Inherits Form

    Dim conn As MySqlConnection = New MySqlConnection("Server=localhost;Database=lakbayph_web;Uid=root;Pwd=;")
    Public sql As String
    Public dbcomm As MySqlCommand

    Private txtFirstName As TextBox
    Private txtLastName As TextBox
    Private txtEmail As TextBox
    Private txtUsername As TextBox
    Private cmbGender As ComboBox
    Private txtPassword As TextBox
    Private txtConfirmPassword As TextBox
    Private btnSignUp As Button
    Private btnCancel As Button
    Private btnClear As Button

    Public Sub New()
        InitializeComponent()
        SetupForm()
        CreateControls()
        SetupEventHandlers()
    End Sub

    Private Sub InitializeComponent()
        Me.SuspendLayout()
        '
        'SignUpForm
        '
        Me.AutoScaleDimensions = New System.Drawing.SizeF(8.0!, 16.0!)
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(282, 253)
        Me.Name = "SignUpForm"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen
        Me.Text = "LakbayPH - Sign Up"
        Me.WindowState = System.Windows.Forms.FormWindowState.Maximized
        Me.ResumeLayout(False)

    End Sub

    Private Sub Form_Resized(sender As Object, e As EventArgs) Handles MyBase.Resize
        Me.Controls.Clear()
        CreateControls()
        SetupEventHandlers()
    End Sub

    Private Sub SetupForm()
        Me.BackColor = Color.FromArgb(45, 85, 95)
        Me.FormBorderStyle = FormBorderStyle.FixedSingle
        Me.MaximizeBox = False
        Me.MinimizeBox = False
    End Sub

    Private Sub CreateControls()
        ' Calculate center position based on form width
        Dim formWidth As Integer = Me.ClientSize.Width
        Dim formHeight As Integer = Me.ClientSize.Height
        Dim formContentWidth As Integer = 450
        Dim formContentHeight As Integer = 700

        Dim centerX As Integer = (formWidth - formContentWidth) \ 2
        Dim topOffset As Integer = (formHeight - formContentHeight) \ 2

        ' Title
        Dim lblTitle As New Label()
        lblTitle.Text = "Create Your Account"
        lblTitle.Font = New Font("Arial", 22, FontStyle.Bold)
        lblTitle.ForeColor = Color.White
        lblTitle.Location = New Point(centerX, topOffset + 30)
        lblTitle.Size = New Size(450, 40)
        lblTitle.TextAlign = ContentAlignment.MiddleCenter
        Me.Controls.Add(lblTitle)

        Dim lblSubtitle As New Label()
        lblSubtitle.Text = "Join LakbayPH and start your adventure!"
        lblSubtitle.Font = New Font("Arial", 12, FontStyle.Regular)
        lblSubtitle.ForeColor = Color.LightGray
        lblSubtitle.Location = New Point(centerX, topOffset + 75)
        lblSubtitle.Size = New Size(450, 25)
        lblSubtitle.TextAlign = ContentAlignment.MiddleCenter
        Me.Controls.Add(lblSubtitle)

        ' First Name
        Dim lblFirstName As New Label()
        lblFirstName.Text = "First Name *"
        lblFirstName.Font = New Font("Arial", 11, FontStyle.Bold)
        lblFirstName.ForeColor = Color.White
        lblFirstName.Location = New Point(centerX, topOffset + 120)
        lblFirstName.Size = New Size(120, 25)
        Me.Controls.Add(lblFirstName)

        txtFirstName = New TextBox()
        txtFirstName.Font = New Font("Arial", 12)
        txtFirstName.Location = New Point(centerX, topOffset + 145)
        txtFirstName.Size = New Size(450, 30)
        txtFirstName.BackColor = Color.White
        txtFirstName.ForeColor = Color.Black
        Me.Controls.Add(txtFirstName)

        ' Last Name
        Dim lblLastName As New Label()
        lblLastName.Text = "Last Name *"
        lblLastName.Font = New Font("Arial", 11, FontStyle.Bold)
        lblLastName.ForeColor = Color.White
        lblLastName.Location = New Point(centerX, topOffset + 185)
        lblLastName.Size = New Size(120, 25)
        Me.Controls.Add(lblLastName)

        txtLastName = New TextBox()
        txtLastName.Font = New Font("Arial", 12)
        txtLastName.Location = New Point(centerX, topOffset + 210)
        txtLastName.Size = New Size(450, 30)
        txtLastName.BackColor = Color.White
        txtLastName.ForeColor = Color.Black
        Me.Controls.Add(txtLastName)

        ' Email
        Dim lblEmail As New Label()
        lblEmail.Text = "Email Address *"
        lblEmail.Font = New Font("Arial", 11, FontStyle.Bold)
        lblEmail.ForeColor = Color.White
        lblEmail.Location = New Point(centerX, topOffset + 250)
        lblEmail.Size = New Size(150, 25)
        Me.Controls.Add(lblEmail)

        txtEmail = New TextBox()
        txtEmail.Font = New Font("Arial", 12)
        txtEmail.Location = New Point(centerX, topOffset + 275)
        txtEmail.Size = New Size(450, 30)
        txtEmail.BackColor = Color.White
        txtEmail.ForeColor = Color.Black
        Me.Controls.Add(txtEmail)

        ' Username
        Dim lblUsername As New Label()
        lblUsername.Text = "Username *"
        lblUsername.Font = New Font("Arial", 11, FontStyle.Bold)
        lblUsername.ForeColor = Color.White
        lblUsername.Location = New Point(centerX, topOffset + 315)
        lblUsername.Size = New Size(120, 25)
        Me.Controls.Add(lblUsername)

        txtUsername = New TextBox()
        txtUsername.Font = New Font("Arial", 12)
        txtUsername.Location = New Point(centerX, topOffset + 340)
        txtUsername.Size = New Size(450, 30)
        txtUsername.BackColor = Color.White
        txtUsername.ForeColor = Color.Black
        Me.Controls.Add(txtUsername)

        ' Gender
        Dim lblGender As New Label()
        lblGender.Text = "Gender *"
        lblGender.Font = New Font("Arial", 11, FontStyle.Bold)
        lblGender.ForeColor = Color.White
        lblGender.Location = New Point(centerX, topOffset + 380)
        lblGender.Size = New Size(80, 25)
        Me.Controls.Add(lblGender)

        cmbGender = New ComboBox()
        cmbGender.Font = New Font("Arial", 12)
        cmbGender.Location = New Point(centerX, topOffset + 405)
        cmbGender.Size = New Size(450, 30)
        cmbGender.BackColor = Color.White
        cmbGender.ForeColor = Color.Black
        cmbGender.DropDownStyle = ComboBoxStyle.DropDownList
        cmbGender.Items.AddRange(New String() {"Select Gender", "Male", "Female", "Other", "Prefer not to say"})
        cmbGender.SelectedIndex = 0
        Me.Controls.Add(cmbGender)

        ' Password
        Dim lblPassword As New Label()
        lblPassword.Text = "Password *"
        lblPassword.Font = New Font("Arial", 11, FontStyle.Bold)
        lblPassword.ForeColor = Color.White
        lblPassword.Location = New Point(centerX, topOffset + 445)
        lblPassword.Size = New Size(100, 25)
        Me.Controls.Add(lblPassword)

        txtPassword = New TextBox()
        txtPassword.Font = New Font("Arial", 12)
        txtPassword.Location = New Point(centerX, topOffset + 470)
        txtPassword.Size = New Size(450, 30)
        txtPassword.BackColor = Color.White
        txtPassword.ForeColor = Color.Black
        txtPassword.UseSystemPasswordChar = True
        Me.Controls.Add(txtPassword)

        ' Confirm Password
        Dim lblConfirmPassword As New Label()
        lblConfirmPassword.Text = "Confirm Password *"
        lblConfirmPassword.Font = New Font("Arial", 11, FontStyle.Bold)
        lblConfirmPassword.ForeColor = Color.White
        lblConfirmPassword.Location = New Point(centerX, topOffset + 510)
        lblConfirmPassword.Size = New Size(150, 25)
        Me.Controls.Add(lblConfirmPassword)

        txtConfirmPassword = New TextBox()
        txtConfirmPassword.Font = New Font("Arial", 12)
        txtConfirmPassword.Location = New Point(centerX, topOffset + 535)
        txtConfirmPassword.Size = New Size(450, 30)
        txtConfirmPassword.BackColor = Color.White
        txtConfirmPassword.ForeColor = Color.Black
        txtConfirmPassword.UseSystemPasswordChar = True
        Me.Controls.Add(txtConfirmPassword)

        ' Required fields note
        Dim lblRequired As New Label()
        lblRequired.Text = "* Required fields"
        lblRequired.Font = New Font("Arial", 10, FontStyle.Italic)
        lblRequired.ForeColor = Color.LightGray
        lblRequired.Location = New Point(centerX, topOffset + 580)
        lblRequired.Size = New Size(150, 20)
        Me.Controls.Add(lblRequired)

        ' Buttons - centered as a group
        Dim buttonGroupWidth As Integer = 360
        Dim buttonStartX As Integer = centerX + (450 - buttonGroupWidth) / 2

        btnSignUp = New Button()
        btnSignUp.Text = "SIGN UP"
        btnSignUp.Font = New Font("Arial", 12, FontStyle.Bold)
        btnSignUp.ForeColor = Color.White
        btnSignUp.BackColor = Color.FromArgb(30, 150, 80)
        btnSignUp.FlatStyle = FlatStyle.Flat
        btnSignUp.FlatAppearance.BorderColor = Color.FromArgb(30, 150, 80)
        btnSignUp.FlatAppearance.MouseOverBackColor = Color.FromArgb(25, 120, 65)
        btnSignUp.Location = New Point(buttonStartX, topOffset + 620)
        btnSignUp.Size = New Size(120, 45)
        btnSignUp.Cursor = Cursors.Hand
        Me.Controls.Add(btnSignUp)

        btnClear = New Button()
        btnClear.Text = "CLEAR"
        btnClear.Font = New Font("Arial", 12, FontStyle.Bold)
        btnClear.ForeColor = Color.White
        btnClear.BackColor = Color.FromArgb(200, 100, 50)
        btnClear.FlatStyle = FlatStyle.Flat
        btnClear.FlatAppearance.BorderColor = Color.FromArgb(200, 100, 50)
        btnClear.FlatAppearance.MouseOverBackColor = Color.FromArgb(170, 80, 30)
        btnClear.Location = New Point(buttonStartX + 120, topOffset + 620)
        btnClear.Size = New Size(120, 45)
        btnClear.Cursor = Cursors.Hand
        Me.Controls.Add(btnClear)

        btnCancel = New Button()
        btnCancel.Text = "CANCEL"
        btnCancel.Font = New Font("Arial", 12, FontStyle.Bold)
        btnCancel.ForeColor = Color.White
        btnCancel.BackColor = Color.FromArgb(120, 120, 120)
        btnCancel.FlatStyle = FlatStyle.Flat
        btnCancel.FlatAppearance.BorderColor = Color.FromArgb(120, 120, 120)
        btnCancel.FlatAppearance.MouseOverBackColor = Color.FromArgb(100, 100, 100)
        btnCancel.Location = New Point(buttonStartX + 240, topOffset + 620)
        btnCancel.Size = New Size(120, 45)
        btnCancel.Cursor = Cursors.Hand
        Me.Controls.Add(btnCancel)
    End Sub

    Private Sub SetupEventHandlers()
        ' Only add handlers if controls exist
        If btnSignUp IsNot Nothing Then
            RemoveHandler btnSignUp.Click, AddressOf BtnSignUp_Click
            AddHandler btnSignUp.Click, AddressOf BtnSignUp_Click
        End If

        If btnCancel IsNot Nothing Then
            RemoveHandler btnCancel.Click, AddressOf BtnCancel_Click
            AddHandler btnCancel.Click, AddressOf BtnCancel_Click
        End If

        If btnClear IsNot Nothing Then
            RemoveHandler btnClear.Click, AddressOf BtnClear_Click
            AddHandler btnClear.Click, AddressOf BtnClear_Click
        End If

        If txtUsername IsNot Nothing Then
            RemoveHandler txtUsername.TextChanged, AddressOf TxtUsername_TextChanged
            AddHandler txtUsername.TextChanged, AddressOf TxtUsername_TextChanged
        End If

        If txtEmail IsNot Nothing Then
            RemoveHandler txtEmail.Leave, AddressOf TxtEmail_Leave
            AddHandler txtEmail.Leave, AddressOf TxtEmail_Leave
        End If

        If txtPassword IsNot Nothing Then
            RemoveHandler txtPassword.TextChanged, AddressOf TxtPassword_TextChanged
            AddHandler txtPassword.TextChanged, AddressOf TxtPassword_TextChanged
        End If

        If txtConfirmPassword IsNot Nothing Then
            RemoveHandler txtConfirmPassword.TextChanged, AddressOf TxtConfirmPassword_TextChanged
            AddHandler txtConfirmPassword.TextChanged, AddressOf TxtConfirmPassword_TextChanged
        End If
    End Sub

    Private Sub BtnSignUp_Click(sender As Object, e As EventArgs)
        If ValidateInputs() Then
            Try
                If CheckUserExists(txtUsername.Text, txtEmail.Text) Then
                    MessageBox.Show("Username or email already exists. Please choose different values.",
                                  "Registration Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
                    Return
                End If

                If CreateUserAccount() Then
                    Dim message As String = "Account created successfully!" & vbCrLf & vbCrLf &
                                           "Welcome to LakbayPH, " & txtFirstName.Text & "!" & vbCrLf &
                                           "Username: " & txtUsername.Text & vbCrLf &
                                           "Email: " & txtEmail.Text

                    MessageBox.Show(message, "Success", MessageBoxButtons.OK, MessageBoxIcon.Information)
                    Me.Close()
                End If
            Catch ex As Exception
                MessageBox.Show("Error creating account: " & ex.Message, "Database Error",
                              MessageBoxButtons.OK, MessageBoxIcon.Error)
            End Try
        End If
    End Sub

    Private Function CreateUserAccount() As Boolean
        Try
            ' Check if user exists first
            If CheckUserExists(txtUsername.Text, txtEmail.Text) Then
                MessageBox.Show("Username or email already exists. Please choose different credentials.",
                  "User Exists", MessageBoxButtons.OK, MessageBoxIcon.Warning)
                Return False
            End If

            ' Open connection
            If conn.State = ConnectionState.Closed Then
                conn.Open()
            End If

            Dim hashedPassword As String = HashPassword(txtPassword.Text)

            ' Updated query to match your database schema
            Dim query As String = "INSERT INTO userss (FirstName, LastName, Email, Username, Password_, CreatedAt) VALUES (@FirstName, @LastName, @Email, @Username, @Password, @CreatedAt)"

            ' Initialize command properly
            Using dbcomm As New MySqlCommand(query, conn)
                ' Add parameters
                dbcomm.Parameters.AddWithValue("@FirstName", If(String.IsNullOrWhiteSpace(txtFirstName.Text), DBNull.Value, txtFirstName.Text.Trim()))
                dbcomm.Parameters.AddWithValue("@LastName", If(String.IsNullOrWhiteSpace(txtLastName.Text), DBNull.Value, txtLastName.Text.Trim()))
                dbcomm.Parameters.AddWithValue("@Email", txtEmail.Text.Trim().ToLower())
                dbcomm.Parameters.AddWithValue("@Username", txtUsername.Text.Trim().ToLower())
                dbcomm.Parameters.AddWithValue("@Password", hashedPassword)
                dbcomm.Parameters.AddWithValue("@CreatedAt", DateTime.Now)

                Dim rowsAffected As Integer = dbcomm.ExecuteNonQuery()

                If rowsAffected > 0 Then
                    Return True
                Else
                    MessageBox.Show("Failed to create user account. No rows affected.", "Error",
                      MessageBoxButtons.OK, MessageBoxIcon.Error)
                    Return False
                End If
            End Using

        Catch ex As MySqlException
            Select Case ex.Number
                Case 1062
                    MessageBox.Show("Username or email already exists. Please choose different credentials.",
                      "Duplicate Entry", MessageBoxButtons.OK, MessageBoxIcon.Warning)
                Case 2003
                    MessageBox.Show("Cannot connect to database server. Please check your connection.",
                      "Connection Error", MessageBoxButtons.OK, MessageBoxIcon.Error)
                Case Else
                    MessageBox.Show($"Database error: {ex.Message}", "Database Error",
                      MessageBoxButtons.OK, MessageBoxIcon.Error)
            End Select
            Return False

        Catch ex As Exception
            MessageBox.Show($"Unexpected error: {ex.Message}", "Error",
              MessageBoxButtons.OK, MessageBoxIcon.Error)
            Return False
        Finally
            ' Close connection
            If conn.State = ConnectionState.Open Then
                conn.Close()
            End If
        End Try
    End Function

    ' FIXED: CheckUserExists function - properly initialize MySqlCommand
    Private Function CheckUserExists(username As String, email As String) As Boolean
        Try
            ' Open connection if closed
            If conn.State = ConnectionState.Closed Then
                conn.Open()
            End If

            Dim query As String = "SELECT COUNT(*) FROM userss WHERE Username = @Username OR Email = @Email"

            ' Create a new MySqlCommand with proper initialization
            Using dbcomm As New MySqlCommand(query, conn)
                dbcomm.Parameters.AddWithValue("@Username", username.Trim().ToLower())
                dbcomm.Parameters.AddWithValue("@Email", email.Trim().ToLower())

                Dim count As Integer = Convert.ToInt32(dbcomm.ExecuteScalar())
                Return count > 0
            End Using

        Catch ex As Exception
            MessageBox.Show("Error checking user existence: " & ex.Message, "Database Error",
                      MessageBoxButtons.OK, MessageBoxIcon.Error)
            Return True ' Return True to prevent duplicate creation on error
        Finally
            ' Close connection
            If conn.State = ConnectionState.Open Then
                conn.Close()
            End If
        End Try
    End Function

    Private Function HashPassword(password As String) As String
        Try
            ' Input validation
            If String.IsNullOrEmpty(password) Then
                Throw New ArgumentException("Password cannot be null or empty")
            End If

            Using sha256Hash As SHA256 = SHA256.Create()
                ' ComputeHash - returns byte array
                Dim bytes As Byte() = sha256Hash.ComputeHash(Encoding.UTF8.GetBytes(password))

                ' Convert byte array to a string using StringBuilder for better performance
                Dim builder As New StringBuilder(bytes.Length * 2)
                For Each b As Byte In bytes
                    builder.Append(b.ToString("x2"))
                Next

                Return builder.ToString()
            End Using

        Catch ex As Exception
            ' Log the error for debugging purposes
            MessageBox.Show($"Error hashing password: {ex.Message}", "Hash Error",
                      MessageBoxButtons.OK, MessageBoxIcon.Error)

            Throw New InvalidOperationException("Password hashing failed", ex)
        End Try
    End Function

    Private Sub BtnCancel_Click(sender As Object, e As EventArgs)
        Dim result As DialogResult = MessageBox.Show("Are you sure you want to cancel? All entered data will be lost.",
                                                    "Cancel Registration",
                                                    MessageBoxButtons.YesNo,
                                                    MessageBoxIcon.Question)
        If result = DialogResult.Yes Then
            Me.Close()
        End If
    End Sub

    Private Sub BtnClear_Click(sender As Object, e As EventArgs)
        ClearAllFields()
    End Sub

    Private Sub TxtUsername_TextChanged(sender As Object, e As EventArgs)
        ' Remove spaces and convert to lowercase for username
        If txtUsername IsNot Nothing Then
            Dim cursorPosition As Integer = txtUsername.SelectionStart
            Dim newText As String = txtUsername.Text.Replace(" ", "").ToLower()
            If txtUsername.Text <> newText Then
                txtUsername.Text = newText
                txtUsername.SelectionStart = Math.Min(cursorPosition, txtUsername.Text.Length)
            End If
        End If
    End Sub

    Private Sub TxtEmail_Leave(sender As Object, e As EventArgs)
        ' email validation
        If txtEmail IsNot Nothing AndAlso Not String.IsNullOrWhiteSpace(txtEmail.Text) Then
            If Not IsValidEmail(txtEmail.Text) Then
                txtEmail.BackColor = Color.LightPink
            Else
                txtEmail.BackColor = Color.White
            End If
        End If
    End Sub

    Private Sub TxtPassword_TextChanged(sender As Object, e As EventArgs)
        ' Password strength indicator
        If txtPassword IsNot Nothing Then
            If txtPassword.Text.Length >= 8 Then
                txtPassword.BackColor = Color.LightGreen
            ElseIf txtPassword.Text.Length >= 6 Then
                txtPassword.BackColor = Color.LightYellow
            ElseIf txtPassword.Text.Length > 0 Then
                txtPassword.BackColor = Color.LightPink
            Else
                txtPassword.BackColor = Color.White
            End If
        End If
    End Sub

    Private Sub TxtConfirmPassword_TextChanged(sender As Object, e As EventArgs)
        ' Check if passwords match
        If txtConfirmPassword IsNot Nothing AndAlso txtPassword IsNot Nothing Then
            If txtConfirmPassword.Text.Length > 0 Then
                If txtPassword.Text = txtConfirmPassword.Text Then
                    txtConfirmPassword.BackColor = Color.LightGreen
                Else
                    txtConfirmPassword.BackColor = Color.LightPink
                End If
            Else
                txtConfirmPassword.BackColor = Color.White
            End If
        End If
    End Sub

    Private Function ValidateInputs() As Boolean
        ' Check if controls exist first
        If txtFirstName Is Nothing OrElse txtLastName Is Nothing OrElse
           txtEmail Is Nothing OrElse txtUsername Is Nothing OrElse
           cmbGender Is Nothing OrElse txtPassword Is Nothing OrElse
           txtConfirmPassword Is Nothing Then
            Return False
        End If

        ' Reset all background colors
        txtFirstName.BackColor = Color.White
        txtLastName.BackColor = Color.White
        txtEmail.BackColor = Color.White
        txtUsername.BackColor = Color.White

        ' Validate First Name
        If String.IsNullOrWhiteSpace(txtFirstName.Text) Then
            MessageBox.Show("Please enter your first name.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtFirstName.BackColor = Color.LightPink
            txtFirstName.Focus()
            Return False
        End If

        ' Validate Last Name
        If String.IsNullOrWhiteSpace(txtLastName.Text) Then
            MessageBox.Show("Please enter your last name.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtLastName.BackColor = Color.LightPink
            txtLastName.Focus()
            Return False
        End If

        ' Validate Email
        If String.IsNullOrWhiteSpace(txtEmail.Text) Then
            MessageBox.Show("Please enter your email address.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtEmail.BackColor = Color.LightPink
            txtEmail.Focus()
            Return False
        End If

        If Not IsValidEmail(txtEmail.Text) Then
            MessageBox.Show("Please enter a valid email address.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtEmail.BackColor = Color.LightPink
            txtEmail.Focus()
            Return False
        End If

        ' Validate Username
        If String.IsNullOrWhiteSpace(txtUsername.Text) Then
            MessageBox.Show("Please enter a username.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtUsername.BackColor = Color.LightPink
            txtUsername.Focus()
            Return False
        End If

        If txtUsername.Text.Length < 3 Then
            MessageBox.Show("Username must be at least 3 characters long.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtUsername.BackColor = Color.LightPink
            txtUsername.Focus()
            Return False
        End If

        ' Validate Gender
        If cmbGender.SelectedIndex = 0 Then
            MessageBox.Show("Please select your gender.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            cmbGender.Focus()
            Return False
        End If

        ' Validate Password
        If String.IsNullOrWhiteSpace(txtPassword.Text) Then
            MessageBox.Show("Please enter a password.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtPassword.Focus()
            Return False
        End If

        If txtPassword.Text.Length < 6 Then
            MessageBox.Show("Password must be at least 6 characters long.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtPassword.Focus()
            Return False
        End If

        ' Validate Confirm Password
        If txtPassword.Text <> txtConfirmPassword.Text Then
            MessageBox.Show("Passwords do not match.", "Validation Error", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            txtConfirmPassword.Focus()
            Return False
        End If

        Return True
    End Function

    Private Function IsValidEmail(email As String) As Boolean
        Try
            Dim addr As New System.Net.Mail.MailAddress(email)
            Return addr.Address = email
        Catch
            Return False
        End Try
    End Function

    Private Sub ClearAllFields()
        ' Check if controls exist first
        If txtFirstName IsNot Nothing Then txtFirstName.Clear()
        If txtLastName IsNot Nothing Then txtLastName.Clear()
        If txtEmail IsNot Nothing Then txtEmail.Clear()
        If txtUsername IsNot Nothing Then txtUsername.Clear()
        If cmbGender IsNot Nothing Then cmbGender.SelectedIndex = 0
        If txtPassword IsNot Nothing Then txtPassword.Clear()
        If txtConfirmPassword IsNot Nothing Then txtConfirmPassword.Clear()

        ' Reset background colors
        If txtFirstName IsNot Nothing Then txtFirstName.BackColor = Color.White
        If txtLastName IsNot Nothing Then txtLastName.BackColor = Color.White
        If txtEmail IsNot Nothing Then txtEmail.BackColor = Color.White
        If txtUsername IsNot Nothing Then txtUsername.BackColor = Color.White
        If txtPassword IsNot Nothing Then txtPassword.BackColor = Color.White
        If txtConfirmPassword IsNot Nothing Then txtConfirmPassword.BackColor = Color.White

        If txtFirstName IsNot Nothing Then txtFirstName.Focus()
    End Sub

    Private Sub SignUpForm_Load(sender As Object, e As EventArgs) Handles MyBase.Load

    End Sub
End Class