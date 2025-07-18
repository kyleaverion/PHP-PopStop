Imports System.Drawing
Imports System.Windows.Forms
Imports System.IO

Public Class AdventurerHomeForm
    Inherits Form

    Private leftPanel As Panel
    Private rightPanel As Panel
    Private gridPanel As TableLayoutPanel
    Private adventureCards As New List(Of AdventureCard)
    Private addNewCard As Panel
    Private user As MainForm.UserInfo

    Public Sub New()
        InitializeComponent()
        SetupUI()
        LoadSampleData()
    End Sub

    Public Sub New(user As MainForm.UserInfo)
        Me.user = user
    End Sub

    Private Sub InitializeComponent()
        Me.SuspendLayout()
        '
        'AdventurerHomeForm
        '
        Me.BackColor = System.Drawing.Color.FromArgb(CType(CType(44, Byte), Integer), CType(CType(62, Byte), Integer), CType(CType(80, Byte), Integer))
        Me.ClientSize = New System.Drawing.Size(1920, 1080)
        Me.FormBorderStyle = System.Windows.Forms.FormBorderStyle.None
        Me.MaximizeBox = False
        Me.Name = "AdventurerHomeForm"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen
        Me.Text = "Adventurer's Journal"
        Me.ResumeLayout(False)

    End Sub

    Private Sub SetupUI()
        ' Create main container
        Dim mainContainer As New TableLayoutPanel()
        mainContainer.Dock = DockStyle.Fill
        mainContainer.ColumnCount = 2
        mainContainer.RowCount = 1
        mainContainer.ColumnStyles.Add(New ColumnStyle(SizeType.Percent, 50))
        mainContainer.ColumnStyles.Add(New ColumnStyle(SizeType.Percent, 50))

        ' Setup left panel
        SetupLeftPanel()
        mainContainer.Controls.Add(leftPanel, 0, 0)

        ' Setup right panel
        SetupRightPanel()
        mainContainer.Controls.Add(rightPanel, 1, 0)

        Me.Controls.Add(mainContainer)
    End Sub

    Private Sub SetupLeftPanel()
        leftPanel = New Panel()
        leftPanel.Dock = DockStyle.Fill
        leftPanel.BackColor = Color.FromArgb(52, 73, 94)
        leftPanel.Padding = New Padding(40)

        ' Back button - positioned at the top left
        Dim btnBack As New Button()
        btnBack.Text = "← Back"
        btnBack.Size = New Size(80, 35)
        btnBack.Location = New Point(20, 20)
        btnBack.BackColor = Color.FromArgb(149, 165, 166)
        btnBack.ForeColor = Color.White
        btnBack.FlatStyle = FlatStyle.Flat
        btnBack.FlatAppearance.BorderSize = 0
        btnBack.Cursor = Cursors.Hand
        btnBack.Font = New Font("Arial", 10, FontStyle.Bold)
        AddHandler btnBack.Click, AddressOf BackButton_Click

        ' Main quote label
        Dim quoteLabel As New Label()
        quoteLabel.Text = "EXPLORE THE" & vbCrLf & "UNKNOWN"
        quoteLabel.Font = New Font("Arial", 36, FontStyle.Bold)
        quoteLabel.ForeColor = Color.White
        quoteLabel.AutoSize = True
        quoteLabel.Location = New Point(40, 150)

        ' Subtitle
        Dim subtitleLabel As New Label()
        subtitleLabel.Text = "Every journey begins with a single step." & vbCrLf & "Document your adventures and share" & vbCrLf & "the wisdom you've gained along the way."
        subtitleLabel.Font = New Font("Arial", 12, FontStyle.Regular)
        subtitleLabel.ForeColor = Color.FromArgb(189, 195, 199)
        subtitleLabel.AutoSize = True
        subtitleLabel.Location = New Point(40, 300)

        ' CTA Button
        Dim ctaButton As New Button()
        ctaButton.Text = "START ADVENTURE"
        ctaButton.Size = New Size(200, 50)
        ctaButton.Location = New Point(40, 400)
        ctaButton.BackColor = Color.FromArgb(231, 76, 60)
        ctaButton.ForeColor = Color.White
        ctaButton.Font = New Font("Arial", 11, FontStyle.Bold)
        ctaButton.FlatStyle = FlatStyle.Flat
        ctaButton.FlatAppearance.BorderSize = 0
        ctaButton.Cursor = Cursors.Hand
        AddHandler ctaButton.Click, AddressOf StartAdventure_Click

        leftPanel.Controls.AddRange({btnBack, quoteLabel, subtitleLabel, ctaButton})
    End Sub

    Private Sub SetupRightPanel()
        rightPanel = New Panel()
        rightPanel.Dock = DockStyle.Fill
        rightPanel.BackColor = Color.FromArgb(236, 240, 241)
        rightPanel.Padding = New Padding(20)

        ' Grid for adventure cards
        gridPanel = New TableLayoutPanel()
        gridPanel.Dock = DockStyle.Fill
        gridPanel.ColumnCount = 2
        gridPanel.RowCount = 2
        gridPanel.ColumnStyles.Add(New ColumnStyle(SizeType.Percent, 50))
        gridPanel.ColumnStyles.Add(New ColumnStyle(SizeType.Percent, 50))
        gridPanel.RowStyles.Add(New RowStyle(SizeType.Percent, 50))
        gridPanel.RowStyles.Add(New RowStyle(SizeType.Percent, 50))

        ' Add "Add New" card
        SetupAddNewCard()
        gridPanel.Controls.Add(addNewCard, 0, 0)

        rightPanel.Controls.Add(gridPanel)
    End Sub

    Private Sub SetupAddNewCard()
        addNewCard = New Panel()
        addNewCard.Margin = New Padding(10)
        addNewCard.BackColor = Color.White
        addNewCard.BorderStyle = BorderStyle.FixedSingle
        addNewCard.Cursor = Cursors.Hand

        Dim addLabel As New Label()
        addLabel.Text = "+" & vbCrLf & "ADD NEW ADVENTURE"
        addLabel.Font = New Font("Arial", 14, FontStyle.Bold)
        addLabel.ForeColor = Color.FromArgb(127, 140, 141)
        addLabel.TextAlign = ContentAlignment.MiddleCenter
        addLabel.Dock = DockStyle.Fill

        addNewCard.Controls.Add(addLabel)
        AddHandler addNewCard.Click, AddressOf AddNewCard_Click
        AddHandler addLabel.Click, AddressOf AddNewCard_Click
    End Sub

    Private Sub LoadSampleData()
        ' Sample adventure cards
        CreateAdventureCard("The path less traveled often leads to the most beautiful destinations.", "Mountain Explorer", Color.FromArgb(142, 68, 173))
        CreateAdventureCard("Adventure awaits those brave enough to seek it beyond their comfort zone.", "Desert Wanderer", Color.FromArgb(39, 174, 96))
        CreateAdventureCard("Every sunset brings the promise of a new dawn and fresh possibilities.", "Coastal Adventurer", Color.FromArgb(230, 126, 34))
    End Sub

    Private Sub CreateAdventureCard(quote As String, author As String, bgColor As Color)
        Dim card As New AdventureCard(quote, author, bgColor)
        adventureCards.Add(card)

        ' Find next available position
        Dim position As Point = GetNextGridPosition()
        gridPanel.Controls.Add(card.Panel, position.X, position.Y)
    End Sub

    Private Function GetNextGridPosition() As Point
        For row As Integer = 0 To gridPanel.RowCount - 1
            For col As Integer = 0 To gridPanel.ColumnCount - 1
                If gridPanel.GetControlFromPosition(col, row) Is Nothing Then
                    Return New Point(col, row)
                End If
            Next
        Next
        Return New Point(0, 0)
    End Function

    Private Sub AddNewCard_Click(sender As Object, e As EventArgs)
        Dim addForm As New AddAdventureForm()
        If addForm.ShowDialog() = DialogResult.OK Then
            CreateAdventureCard(addForm.Quote, addForm.Author, addForm.SelectedColr)
        End If
    End Sub

    Private Sub StartAdventure_Click(sender As Object, e As EventArgs)
        ' Open the LakbayPH Packages Form
        Dim packagesForm As New MainForm()
        Me.Hide() ' Hide the current form
        packagesForm.ShowDialog() ' Show the packages form as a dialog
        Me.Show() ' Show the main form again when packages form is closed
    End Sub
    Private Sub BackButton_Click(sender As Object, e As EventArgs)
        ' Close the main form and exit the application
        Me.Close()
    End Sub

    Private Sub AdventurerHomeForm_Load(sender As Object, e As EventArgs) Handles MyBase.Load

    End Sub
End Class

Public Class AdventureCard
    Public Property Panel As Panel
    Public Property Quote As String
    Public Property Author As String
    Public Property BackgroundColor As Color

    Public Sub New(quote As String, author As String, bgColor As Color)
        Me.Quote = quote
        Me.Author = author
        Me.BackgroundColor = bgColor
        CreatePanel()
    End Sub

    Private Sub CreatePanel()
        Panel = New Panel()
        Panel.Margin = New Padding(10)
        Panel.BackColor = BackgroundColor
        Panel.Cursor = Cursors.Hand

        ' Quote label
        Dim quoteLabel As New Label()
        quoteLabel.Text = Quote
        quoteLabel.Font = New Font("Arial", 11, FontStyle.Italic)
        quoteLabel.ForeColor = Color.White
        quoteLabel.Location = New Point(15, 15)
        quoteLabel.Size = New Size(Panel.Width - 30, 120)
        quoteLabel.AutoSize = False

        ' Author label
        Dim authorLabel As New Label()
        authorLabel.Text = "- " & Author
        authorLabel.Font = New Font("Arial", 10, FontStyle.Bold)
        authorLabel.ForeColor = Color.FromArgb(236, 240, 241)
        authorLabel.Location = New Point(15, 140)
        authorLabel.AutoSize = True

        Panel.Controls.AddRange({quoteLabel, authorLabel})

        ' Add click event
        AddHandler Panel.Click, AddressOf Card_Click
        AddHandler quoteLabel.Click, AddressOf Card_Click
        AddHandler authorLabel.Click, AddressOf Card_Click
    End Sub

    Private Sub Card_Click(sender As Object, e As EventArgs)
        MessageBox.Show(Quote & vbCrLf & vbCrLf & "- " & Author, "Adventure Quote", MessageBoxButtons.OK, MessageBoxIcon.Information)
    End Sub
End Class

Public Class AddAdventureForm
    Inherits Form

    Private txtQuote As TextBox
    Private txtAuthor As TextBox
    Private colorPanel As Panel
    Private selectedColor As Color = Color.FromArgb(52, 152, 219)

    Public Property Quote As String
    Public Property Author As String
    Public Property SelectedColr As Color

    Public Sub New()
        InitializeComponent()
        SetupUI()
    End Sub

    Private Sub InitializeComponent()
        Me.Text = "Add New Adventure"
        Me.Size = New Size(500, 350)
        Me.StartPosition = FormStartPosition.CenterParent
        Me.FormBorderStyle = FormBorderStyle.FixedDialog
        Me.MaximizeBox = False
        Me.MinimizeBox = False
        Me.BackColor = Color.White
    End Sub

    Private Sub SetupUI()
        ' Quote input
        Dim lblQuote As New Label()
        lblQuote.Text = "Adventure Quote:"
        lblQuote.Font = New Font("Arial", 10, FontStyle.Bold)
        lblQuote.Location = New Point(20, 20)
        lblQuote.Size = New Size(200, 20)

        txtQuote = New TextBox()
        txtQuote.Multiline = True
        txtQuote.Location = New Point(20, 45)
        txtQuote.Size = New Size(440, 80)
        txtQuote.Font = New Font("Arial", 10)

        ' Author input
        Dim lblAuthor As New Label()
        lblAuthor.Text = "Author/Explorer:"
        lblAuthor.Font = New Font("Arial", 10, FontStyle.Bold)
        lblAuthor.Location = New Point(20, 140)
        lblAuthor.Size = New Size(200, 20)

        txtAuthor = New TextBox()
        txtAuthor.Location = New Point(20, 165)
        txtAuthor.Size = New Size(440, 25)
        txtAuthor.Font = New Font("Arial", 10)

        ' Color selection
        Dim lblColor As New Label()
        lblColor.Text = "Card Color:"
        lblColor.Font = New Font("Arial", 10, FontStyle.Bold)
        lblColor.Location = New Point(20, 200)
        lblColor.Size = New Size(200, 20)

        colorPanel = New Panel()
        colorPanel.Location = New Point(20, 225)
        colorPanel.Size = New Size(440, 30)
        colorPanel.BackColor = selectedColor
        colorPanel.BorderStyle = BorderStyle.FixedSingle
        colorPanel.Cursor = Cursors.Hand
        AddHandler colorPanel.Click, AddressOf ColorPanel_Click

        ' Buttons
        Dim btnOK As New Button()
        btnOK.Text = "Add Adventure"
        btnOK.Size = New Size(100, 30)
        btnOK.Location = New Point(280, 270)
        btnOK.BackColor = Color.FromArgb(39, 174, 96)
        btnOK.ForeColor = Color.White
        btnOK.FlatStyle = FlatStyle.Flat
        btnOK.DialogResult = DialogResult.OK
        AddHandler btnOK.Click, AddressOf BtnOK_Click

        Dim btnCancel As New Button()
        btnCancel.Text = "Cancel"
        btnCancel.Size = New Size(80, 30)
        btnCancel.Location = New Point(390, 270)
        btnCancel.BackColor = Color.FromArgb(231, 76, 60)
        btnCancel.ForeColor = Color.White
        btnCancel.FlatStyle = FlatStyle.Flat
        btnCancel.DialogResult = DialogResult.Cancel

        Me.Controls.AddRange({lblQuote, txtQuote, lblAuthor, txtAuthor, lblColor, colorPanel, btnOK, btnCancel})
    End Sub

    Private Sub ColorPanel_Click(sender As Object, e As EventArgs)
        Dim colorDialog As New ColorDialog()
        colorDialog.Color = selectedColor
        If colorDialog.ShowDialog() = DialogResult.OK Then
            selectedColor = colorDialog.Color
            colorPanel.BackColor = selectedColor
        End If
    End Sub

    Private Sub BtnOK_Click(sender As Object, e As EventArgs)
        If String.IsNullOrWhiteSpace(txtQuote.Text) OrElse String.IsNullOrWhiteSpace(txtAuthor.Text) Then
            MessageBox.Show("Please fill in both quote and author fields.", "Missing Information", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            Return
        End If

        Quote = txtQuote.Text.Trim()
        Author = txtAuthor.Text.Trim()
        SelectedColr = selectedColor
    End Sub
End Class

' Main application entry point
Module HomeForm
    <STAThread>
    Sub Main()
        Application.EnableVisualStyles()
        Application.SetCompatibleTextRenderingDefault(False)
        Application.Run(New AdventurerHomeForm())
    End Sub
End Module